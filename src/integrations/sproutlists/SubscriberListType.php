<?php

namespace barrelstrength\sproutlists\integrations\sproutlists;

use barrelstrength\sproutbase\contracts\sproutlists\SproutListsBaseListType;
use barrelstrength\sproutlists\elements\Lists;
use barrelstrength\sproutlists\elements\Subscribers;
use barrelstrength\sproutlists\models\Subscription;
use barrelstrength\sproutlists\records\Subscription as SubscriptionRecord;
use barrelstrength\sproutlists\SproutLists;
use Craft;
use craft\helpers\Template;
use barrelstrength\sproutlists\records\Subscribers as SubscribersRecord;
use barrelstrength\sproutlists\records\Lists as ListsRecord;

class SubscriberListType extends SproutListsBaseListType
{
    /**
     * @return string
     */
    public function getName()
    {
        return Craft::t('sprout-lists', 'Subscriber Lists');
    }

    /**
     * The handle that refers to this list. Used as the 'type' when submitting forms.
     *
     * @return string
     */
    public function getHandle()
    {
        return 'subscriber';
    }

    // Lists
    // =========================================================================

    /**
     * Saves a list.
     *
     * @param Lists $list
     *
     * @return bool
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     */
    public function saveList(Lists $list)
    {
        $list->totalSubscribers = 0;

        return Craft::$app->elements->saveElement($list);
    }

    /**
     * Gets lists.
     *
     * @param null $subscriber
     *
     * @return array
     */
    public function getLists($subscriber = null)
    {
        $lists = [];

        $subscriberRecord = null;
        /**
         * @var $subscriber Subscribers
         */
        if ($subscriber != null AND (!empty($subscriber->email) OR !empty($subscriber->userId))) {
            $subscriberAttributes = array_filter([
                'email' => $subscriber->email,
                'userId' => $subscriber->userId
            ]);

            $subscriberRecord = SubscribersRecord::find()->where($subscriberAttributes)->all();
        }
        $listRecords = [];
        if ($subscriberRecord == null) {
            // Only findAll if we are not looking for a specific Subscriber, otherwise we want to return null
            if (empty($subscriber->email)) {
                $listRecords = ListsRecord::find()->all();
            }
        }

        if (!empty($listRecords)) {
            foreach ($listRecords as $listRecord) {
                $list = new Lists();
                $list->setAttributes($listRecord->getAttributes(), false);
                $lists[] = $list;
            }
        }

        return $lists;
    }

    /**
     * @param null $subscriber
     *
     * @return int
     */
    public function getListCount($subscriber = null)
    {
        $lists = $this->getLists($subscriber);

        return count($lists);
    }

    /**
     * Gets list with a given id.
     *
     * @param $listId
     *
     * @return \craft\base\ElementInterface|mixed|null
     */
    public function getListById($listId)
    {
        return Craft::$app->getElements()->getElementById($listId);
    }

    /**
     * Returns an array of all lists that have subscribers.
     *
     * @return array
     */
    public function getListsWithSubscribers()
    {
        $lists = [];
        $records = ListsRecord::find()->all();
        if ($records) {
            foreach ($records as $record) {
                /**
                 * @var $record ListsRecord
                 */
                $subscribers = $record->getSubscribers()->all();

                if (empty($subscribers)) {
                    continue;
                }

                $lists[] = $record;
            }
        }

        return $lists;
    }

    /**
     * Gets or creates list
     *
     * @param Subscription $subscription
     *
     * @return Lists|\craft\base\ElementInterface|null
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     */
    public function getOrCreateList(Subscription $subscription)
    {
        $listRecord = ListsRecord::find()->where(['handle' => $subscription->listHandle])->one();

        $list = new Lists();
        // If no List exists, dynamically create one
        if ($listRecord) {
            $list = Craft::$app->getElements()->getElementById($listRecord->id);
        } else {

            $list->type = SproutLists::$defaultSubscriber;
            $list->elementId = $subscription->elementId;
            $list->name = $subscription->listHandle;
            $list->handle = $subscription->listHandle;

            $this->saveList($list);
        }

        return $list;
    }

    // Subscriptions
    // =========================================================================

    /**
     * @inheritDoc SproutListsBaseListType::subscribe()
     *
     * @param $criteria
     *
     * @return bool
     * @throws \Exception
     */
    public function subscribe($subscription)
    {
        $plugin = Craft::$app->plugins->getPlugin('sprout-lists');

        $settings = null;
        if ($plugin) {
            $settings = $plugin->getSettings();
        }

        $subscriber = new Subscribers();

        if (!empty($subscription->email)) {
            $subscriber->email = $subscription->email;
        }

        if (!empty($subscription->userId) && ($settings AND $settings->enableUserSync)) {
            $subscriber->userId = $subscription->userId;
        }

        try {
            // If our List doesn't exist, create a List Element on the fly
            $list = $this->getOrCreateList($subscription);

            // If our Subscriber doesn't exist, create a Subscriber Element on the fly
            $subscriber = $this->getSubscriber($subscriber);

            $subscriptionRecord = new SubscriptionRecord();
            if ($list) {
                $subscriptionRecord->listId = $list->id;
                $subscriptionRecord->subscriberId = $subscriber->id;

                // Create a criteria between our List Element and Subscriber Element
                if ($subscriptionRecord->save(false)) {

                    $this->updateTotalSubscribersCount($subscriptionRecord->listId);
                }
            }

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @inheritDoc SproutListsBaseListType::unsubscribe()
     *
     * @param $subscription
     *
     * @return bool
     */
    public function unsubscribe($subscription)
    {
        $plugin = Craft::$app->plugins->getPlugin('sprout-lists');

        $settings = (isset($plugin)) ? $plugin->getSettings() : null;

        if ($subscription->id) {
            $list = ListsRecord::findOne($subscription->id);
        } else {
            $list = ListsRecord::find()->where([
                'type' => $subscription->listType,
                'handle' => $subscription->listHandle
            ])->one();
        }

        if (!$list) {
            return false;
        }

        // Determine the subscriber that we will un-subscribe
        $subscriberRecord = new SubscribersRecord();

        if (!empty($subscription->userId) && ($settings AND $settings->enableUserSync)) {
            $subscriberRecord = SubscribersRecord::find()->where([
                'userId' => $subscription->userId
            ])->one();
        } elseif (!empty($subscription->email)) {
            $subscriberRecord = SubscribersRecord::find()->where([
                'email' => $subscription->email
            ])->one();
        }

        if (!isset($subscriberRecord->id)) {
            return false;
        }

        // Delete the subscription that matches the List and Subscriber IDs
        $subscriptions = SubscriptionRecord::deleteAll([
            'listId' => $list->id,
            'subscriberId' => $subscriberRecord->id
        ]);

        if ($subscriptions != null) {
            $this->updateTotalSubscribersCount();

            return true;
        }

        return false;
    }

    /**
     * @inheritDoc SproutListsBaseListType::isSubscribed()
     *
     * @param $criteria
     *
     * @return bool
     */
    public function isSubscribed($subscription)
    {
        $plugin = Craft::$app->plugins->getPlugin('sprout-lists');

        $settings = (isset($plugin)) ? $plugin->getSettings() : null;

        if (empty($subscription->listHandle)) {
            throw new \Exception(Craft::t('sprout-lists', 'Missing argument: `listHandle` is required by the isSubscribed variable'));
        }

        // We need a user ID or an email, however, if User Sync is not enabled, we need an email
        if ((empty($subscription->userId) && empty($subscription->email)) OR
            ($settings AND $settings->enableUserSync == false) && empty($subscription->email)
        ) {
            throw new \Exception(Craft::t('sprout-lists', 'Missing argument: `userId` or `email` are required by the isSubscribed variable'));
        }

        $listId = null;
        $subscriberId = null;

        $listRecord = ListsRecord::find()->where([
            'handle' => $subscription->listHandle
        ])->one();

        if ($listRecord) {
            $listId = $listRecord->id;
        }

        $attributes = array_filter([
            'email' => $subscription->email,
            'userId' => $subscription->userId
        ]);

        $subscriberRecord = SubscribersRecord::find()->where($attributes)->one();

        if ($subscriberRecord) {
            $subscriberId = $subscriberRecord->id;
        }

        if ($listId != null && $subscriberId != null) {
            $subscriptionRecord = SubscriptionRecord::find()->where([
                'subscriberId' => $subscriberId,
                'listId' => $listId
            ])->one();

            if ($subscriptionRecord) {
                return true;
            }
        }

        return false;
    }

    /**
     * Saves a subscribers subscriptions.
     *
     * @param Subscribers $subscriber
     *
     * @return bool
     * @throws \Exception
     */
    public function saveSubscriptions(Subscribers $subscriber)
    {
        try {
            SubscriptionRecord::deleteAll('subscriberId = :subscriberId', [
                ':subscriberId' => $subscriber->id
            ]);

            if (!empty($subscriber->subscriberLists)) {
                foreach ($subscriber->subscriberLists as $listId) {
                    $list = $this->getListById($listId);

                    if ($list) {
                        $subscriptionRecord = new SubscriptionRecord();
                        $subscriptionRecord->subscriberId = $subscriber->id;
                        $subscriptionRecord->listId = $list->id;

                        if (!$subscriptionRecord->save(false)) {
                            throw new \Exception(print_r($subscriptionRecord->getErrors(), true));
                        }
                    } else {
                        throw new \Exception(Craft::t('The Subscriber List with id {listId} does not exists.', $listId));
                    }
                }
            }

            $this->updateTotalSubscribersCount();

            return true;
        } catch (\Exception $e) {
            Craft::error($e->getMessage());
            throw $e;
        }
    }

    // Subscribers
    // =========================================================================

    /**
     * @param Subscribers $subscriber
     *
     * @return bool
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     */
    public function saveSubscriber(Subscribers $subscriber)
    {
        if ($subscriber->validate(null, false)) {
            if (Craft::$app->getElements()->saveElement($subscriber)) {
                $this->saveSubscriptions($subscriber);
            }

            return true;
        }

        return false;
    }

    /**
     * Gets a subscriber
     *
     * @param Subscribers $subscriber
     *
     * @return Subscribers|\craft\services\Elements
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     */
    public function getSubscriber(Subscribers $subscriber)
    {
        $attributes = array_filter([
            'email' => $subscriber->email,
            'userId' => $subscriber->userId
        ]);

        $subscriberRecord = SubscribersRecord::find()->where($attributes)->one();

        if (!empty($subscriberRecord)) {
            $subscriber = Craft::$app->getElements()->getElementById($subscriberRecord->id);
        }

        // If no Subscriber was found, create one
        if (!$subscriber->id) {
            if (isset($subscriber->userId)) {
                $user = Craft::$app->users->getUserById($subscriber->userId);

                if ($user) {
                    $subscriber->email = $user->email;
                }
            }

            $this->saveSubscriber($subscriber);
        }

        return $subscriber;
    }

    /**
     * Gets a subscriber with a given id.
     *
     * @param $id
     *
     * @return \craft\base\ElementInterface|null
     */
    public function getSubscriberById($id)
    {
        return Craft::$app->getElements()->getElementById($id);
    }

    /**
     * Deletes a subscriber.
     *
     * @param $id
     *
     * @return \craft\base\ElementInterface|null
     * @throws \Throwable
     */
    public function deleteSubscriberById($id)
    {
        /**
         * @var $subscriber SubscriptionRecord
         */
        $subscriber = $this->getSubscriberById($id);

        if ($subscriber AND ($subscriber AND $subscriber != null)) {
            SproutLists::$app->subscribers->deleteSubscribers($id);
        }

        $this->updateTotalSubscribersCount();

        return $subscriber;
    }

    /**
     * Gets the HTML output for the lists sidebar on the Subscriber edit page.
     *
     * @param $subscriberId
     *
     * @return \Twig_Markup
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    /**
     * @param $subscriberId
     *
     * @return \Twig_Markup
     * @throws \Exception
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getSubscriberListsHtml($subscriberId)
    {
        $default = [];
        $listIds = [];

        if ($subscriberId != null) {

            $subscriber = $this->getSubscriberById($subscriberId);

            if ($subscriber) {
                /**
                 * @var $subscriber Subscribers
                 */
                $listIds = $subscriber->getListIds();
            }
        }

        $lists = $this->getLists();

        $options = [];

        if (count($lists)) {
            foreach ($lists as $list) {
                $options[] = [
                    'label' => sprintf('%s', $list->name),
                    'value' => $list->id
                ];
            }
        }

        if (!empty($default)) {
            $listIds = $default;
        }

        $html = Craft::$app->getView()->renderTemplate('sprout-lists/subscribers/_subscriptionlists', [
            'options' => $options,
            'values' => $listIds
        ]);

        return Template::raw($html);
    }

    /**
     * Updates the totalSubscribers column in the db
     *
     * @param null $listId
     *
     * @return bool
     */
    public function updateTotalSubscribersCount($listId = null)
    {
        if ($listId == null) {
            $lists = ListsRecord::find()->all();
        } else {
            $list = ListsRecord::findOne($listId);

            $lists = [$list];
        }

        if (count($lists)) {
            foreach ($lists as $list) {

                if (!$list) {
                    continue;
                }

                $count = count($list->getSubscribers()->all());

                $list->totalSubscribers = $count;

                $list->save();
            }

            return true;
        }

        return false;
    }

    /**
     * @param $list
     *
     * @return int|mixed
     * @throws \Exception
     */
    public function getSubscriberCount($list)
    {
        $subscribers = $this->getSubscribers($list);

        return count($subscribers);
    }

    /**
     * @param $list
     *
     * @return array|mixed
     * @throws \Exception
     */
    public function getSubscribers($list)
    {
        if (empty($list->type)) {
            throw new \Exception(Craft::t("sprout-lists", "Missing argument: 'type' is required by the getSubscribers variable."));
        }

        if (empty($list->handle)) {
            throw new \Exception(Craft::t("sprout-lists", "Missing argument: 'listHandle' is required by the getSubscribers variable."));
        }

        $subscribers = [];

        if (empty($list)) {
            return $subscribers;
        }

        $listRecord = ListsRecord::find()->where([
            'type' => $list->type,
            'handle' => $list->handle
        ])->one();

        /**
         * @var $listRecord ListsRecord
         */
        if ($listRecord != null) {
            $subscribers = $listRecord->getSubscribers()->all();

            return $subscribers;
        }

        return $subscribers;
    }
}
<?php

namespace barrelstrength\sproutlists\elements;

use barrelstrength\sproutbase\contracts\sproutlists\SproutListsBaseListType;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutlists\elements\actions\DeleteSubscriber;
use barrelstrength\sproutlists\elements\db\SubscribersQuery;
use barrelstrength\sproutlists\records\Subscription;
use barrelstrength\sproutlists\SproutLists;
use craft\base\Element;
use Craft;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\UrlHelper;
use barrelstrength\sproutlists\records\Subscribers as SubscribersRecord;
use craft\validators\UniqueValidator;

class Subscribers extends Element
{
    public $id;
    public $email;
    public $firstName;
    public $lastName;
    public $userId;
    public $subscriberLists;
    public $listType;
    private $subscriberListsIds;

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->email;
    }

    /**
     * @return string
     */
    public static function displayName(): string
    {
        return Craft::t('', 'Sprout Subscribers');
    }

    /**
     * @inheritdoc
     */
    public static function hasContent(): bool
    {
        return true;
    }


    /**
     * @inheritdoc
     */
    public static function hasStatuses(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getCpEditUrl()
    {
        return UrlHelper::cpUrl(
            'sprout-lists/subscribers/edit/'.$this->id
        );
    }

    /**
     * @param string|null $context
     *
     * @return array
     * @throws \Exception
     */
    protected static function defineSources(string $context = null): array
    {
        $sources = [
            [
                'key' => '*',
                'label' => Craft::t('sprout-lists', 'All Lists')
            ]
        ];

        $listType = SproutLists::$app->lists->getListType(SproutLists::$defaultSubscriber);

        $lists = $listType->getListsWithSubscribers();

        if (!empty($lists)) {
            $sources[] = [
                'heading' => $listType->getName()
            ];

            foreach ($lists as $list) {
                $source = [
                    'key' => 'lists:'.$list->id,
                    'label' => $list->name,
                    'data' => ['handle' => $list->handle],
                    'criteria' => ['listId' => $list->id]
                ];

                $sources[] = $source;
            }
        }

        return $sources;
    }

    /**
     * @inheritdoc
     */
    protected static function defineTableAttributes(): array
    {
        $attributes = [
            'email' => ['label' => Craft::t('sprout-lists', 'Email')],
            'firstName' => ['label' => Craft::t('sprout-lists', 'First Name')],
            'lastName' => ['label' => Craft::t('sprout-lists', 'Last Name')],
            'dateCreated' => ['label' => Craft::t('sprout-lists', 'Date Created')],
            'dateUpdated' => ['label' => Craft::t('sprout-lists', 'Date Updated')]
        ];

        return $attributes;
    }

    /**
     * @return ElementQueryInterface
     */
    public static function find(): ElementQueryInterface
    {
        return new SubscribersQuery(static::class);
    }

    /**
     * @return \craft\models\FieldLayout|null
     */
    public function getFieldLayout()
    {
        return Craft::$app->getFields()->getLayoutByType(static::class);
    }

    /**
     * @param mixed|null $element
     *
     * @throws \Exception
     * @return array|string
     */
    public function getRecipients($element = null)
    {
        return SproutBase::$app->mailers->getRecipients($element, $this);
    }

    /**
     * @return null|string
     */
    public function getUriFormat()
    {
        return 'sprout-lists/{slug}';
    }

    /**
     * @return null|string
     * @throws \yii\base\Exception
     */
    public function getUrl()
    {
        if ($this->uri !== null) {
            return UrlHelper::siteUrl($this->uri, null, null);
        }

        return null;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getListIds()
    {
        if (empty($this->subscriberListsIds)) {
            $subscriberLists = $this->getListsBySubscriberId();

            if (count($subscriberLists)) {
                foreach ($subscriberLists as $list) {
                    $this->subscriberListsIds[] = $list->id;
                }
            }
        }

        return $this->subscriberListsIds;
    }

    /**
     * Gets an array of SproutLists_ListModels to which this subscriber is subscribed.
     *
     * @return array
     * @throws \Exception
     */
    public function getListsBySubscriberId()
    {
        $lists = [];

        $subscriptions = Subscription::find()->where([
            'subscriberId' => $this->id
        ])->all();

        $listType = SproutLists::$app->lists->getListType(SproutLists::$defaultSubscriber);

        if (count($subscriptions)) {
            foreach ($subscriptions as $subscription) {
                /**
                 * @var $listType SproutListsBaseListType
                 */
                $lists[] = $listType->getListById($subscription->listId);
            }
        }

        return $lists;
    }

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email'], 'required'],
            [
                ['email'], UniqueValidator::class,
                'targetClass' => SubscribersRecord::class
            ],
            [['email'], 'email']
        ];
    }

    /**
     * @param bool $isNew
     *
     * @throws \Exception
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     */
    public function afterSave(bool $isNew)
    {
        $plugin = Craft::$app->plugins->getPlugin('sprout-lists');

        if ($plugin) {
            $settings = $plugin->getSettings();
        }

        // Get the list record
        if (!$isNew) {
            $record = SubscribersRecord::findOne($this->id);

            if (!$record) {
                throw new \Exception('Invalid list ID: '.$this->id);
            }
        } else {
            $record = new SubscribersRecord();
            $record->id = $this->id;
        }

        $user = null;
        // Sync updates with Craft User if User Sync enabled
        if ($this->email && ($settings AND $settings->enableUserSync)) {
            $user = Craft::$app->users->getUserByUsernameOrEmail($this->email);
            // Set to null when updating un matched email
            $this->userId = null;

            if ($user != null) {
                $this->userId = $user->id;
            }
        }

        $record->userId = $this->userId;
        $record->email = $this->email;
        $record->firstName = $this->firstName;
        $record->lastName = $this->lastName;

        $result = $record->save(false);

        if ($result) {
            // Sync updates with Craft User if User Sync enabled
            if (($user AND $record->userId != null) && $settings->enableUserSync) {
                // If they changed their Subscriber info, update the Craft User info too
                $user->email = $record->email;
                $user->firstName = $record->firstName;
                $user->lastName = $record->lastName;

                Craft::$app->elements->saveElement($user);
            }
            // Update the entry's descendants, who may be using this entry's URI in their own URIs
            Craft::$app->getElements()->updateElementSlugAndUri($this, true, true);
        }

        parent::afterSave($isNew);
    }

    /**
     * @inheritdoc
     */
    protected static function defineActions(string $source = null): array
    {
        $actions = [];

        $actions[] = DeleteSubscriber::class;

        return $actions;
    }
}
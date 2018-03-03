<?php

namespace barrelstrength\sproutlists\web\twig\variables;

use barrelstrength\sproutlists\elements\Lists;
use barrelstrength\sproutlists\elements\Subscribers;
use barrelstrength\sproutlists\integrations\sproutlists\SubscriberListType;
use barrelstrength\sproutlists\models\Subscription;
use barrelstrength\sproutlists\SproutLists;
use Craft;

class SproutListsVariable
{
    /**
     * Checks if a user is subscribed to a given list.
     * @param $criteria
     *
     * @return mixed
     * @throws \Exception
     */
    public function getIsSubscribed($criteria)
    {
        $subscription = new Subscription();
        $subscription->listType = $criteria['listType'] ?? SubscriberListType::class;
        $subscription->listHandle = $criteria['listHandle'] ?? null;
        $subscription->listId = $criteria['listId'] ?? null;
        $subscription->elementId = $criteria['elementId'] ?? null;
        $subscription->userId = $criteria['userId'] ?? null;
        $subscription->email = $criteria['email'] ?? null;

        $listType = SproutLists::$app->lists->getListType($subscription->listType);

        return $listType->isSubscribed($subscription);
    }

    // Subscriptions
    // =========================================================================

    /**
     * Returns all lists for a given subscriber.
     *
     * @param array $criteria
     *
     * @return mixed
     */
    /**
     * @param array $criteria
     *
     * @return mixed
     * @throws \Exception
     */
    public function getLists($criteria = [])
    {
        $subscriber = new Subscribers();
        $subscriber->listType = $criteria['listType'] ?? SubscriberListType::class;
        $subscriber->email = $criteria['email'] ?? null;
        $subscriber->userId = $criteria['userId'] ?? null;
        $subscriber->firstName = $criteria['firstName'] ?? null;
        $subscriber->lastName = $criteria['lastName'] ?? null;

        return $listType->getLists($subscriber);
    }

    /**
     * Return all subscribers on a given list.
     * @param array $criteria
     *
     * @return mixed
     * @throws \Exception
     */
    public function getSubscribers($criteria = [])
    {
        if (!isset($criteria['listHandle']))
        {
            throw new \InvalidArgumentException(Craft::t('sprout-lists', 'The `listHandle` parameter is required.'));
        }

        $list = new Lists();
        $list->handle = $criteria['listHandle'] ?? null;

        $listType = SproutLists::$app->lists->getListTypeByHandle($list->handle);

        $list->type = get_class($listType);

        return $listType->getSubscribers($list);
    }

    // Counts
    // =========================================================================

    /**
     * Return total subscriptions for a given subscriber.
     * @param array $criteria
     *
     * @return mixed
     * @throws \Exception
     */
    public function getListCount($criteria = [])
    {
        $subscriber = new Subscribers();
        $subscriber->listType = $criteria['listType'] ?? SubscriberListType::class;
        $subscriber->email = $criteria['email'] ?? null;
        $subscriber->userId = $criteria['userId'] ?? null;
        $subscriber->firstName = $criteria['firstName'] ?? null;
        $subscriber->lastName = $criteria['lastName'] ?? null;

        $listType = SproutLists::$app->lists->getListType($subscriber->listType);

        return $listType->getListCount($subscriber);
    }

    /**
     * Return total subscriber count on a given list.
     * @param $criteria
     *
     * @return mixed
     * @throws \Exception
     */
    public function getSubscriberCount($criteria)
    {
        $list = new Lists();
        $list->handle = $criteria['listHandle'] ?? null;

        $listType = SproutLists::$app->lists->getListTypeByHandle($list->handle);

        $list->type = get_class($listType);

        return $listType->getSubscriberCount($list);
    }

    /**
     * @return mixed
     */
    public function getErrors()
    {
        return SproutLists::$app->getErrors();
    }
}
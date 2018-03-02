<?php

namespace barrelstrength\sproutlists\web\twig\variables;

use barrelstrength\sproutlists\elements\Lists;
use barrelstrength\sproutlists\elements\Subscribers;
use barrelstrength\sproutlists\models\Subscription;
use barrelstrength\sproutlists\SproutLists;

class SproutListsVariable
{
    /**
     * Checks if a user is subscribed to a given list.
     *
     * @param $criteria
     *
     * @return mixed
     * @throws \Exception
     */
    public function getIsSubscribed($criteria)
    {
        $subscription = new Subscription();
        $subscription->listType = isset($criteria['listType']) ? $criteria['listType'] : SproutLists::$defaultSubscriber;
        $subscription->listHandle = isset($criteria['listHandle']) ? $criteria['listHandle'] : null;
        $subscription->listId = isset($criteria['listId']) ? $criteria['listId'] : null;
        $subscription->elementId = isset($criteria['elementId']) ? $criteria['elementId'] : null;
        $subscription->userId = isset($criteria['userId']) ? $criteria['userId'] : null;
        $subscription->email = isset($criteria['email']) ? $criteria['email'] : null;

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
        $subscriber->listType = isset($criteria['listType']) ? $criteria['listType'] : SproutLists::$defaultSubscriber;
        $subscriber->email = isset($criteria['email']) ? $criteria['email'] : null;
        $subscriber->userId = isset($criteria['userId']) ? $criteria['userId'] : null;
        $subscriber->firstName = isset($criteria['firstName']) ? $criteria['firstName'] : null;
        $subscriber->lastName = isset($criteria['lastName']) ? $criteria['lastName'] : null;

        $listType = SproutLists::$app->lists->getListType($subscriber->listType);

        return $listType->getLists($subscriber);
    }

    /**
     * Return all subscribers on a given list.
     *
     * @param array $criteria
     *
     * @return mixed
     * @throws \Exception
     */
    public function getSubscribers($criteria = [])
    {
        $list = new Lists();
        $listTypeParam = isset($criteria['listType']) ? $criteria['listType'] : SproutLists::$defaultSubscriber;
        $list->handle = isset($criteria['listHandle']) ? $criteria['listHandle'] : null;

        $listType = SproutLists::$app->lists->getListType($listTypeParam);
        $list->type = get_class($listType);
        return $listType->getSubscribers($list);
    }

    // Counts
    // =========================================================================

    /**
     * Return total subscriptions for a given subscriber.
     *
     * @param array $criteria
     *
     * @return mixed
     * @throws \Exception
     */
    public function getListCount($criteria = [])
    {
        $subscriber = new Subscribers();
        $subscriber->listType = isset($criteria['listType']) ? $criteria['listType'] : SproutLists::$defaultSubscriber;
        $subscriber->email = isset($criteria['email']) ? $criteria['email'] : null;
        $subscriber->userId = isset($criteria['userId']) ? $criteria['userId'] : null;
        $subscriber->firstName = isset($criteria['firstName']) ? $criteria['firstName'] : null;
        $subscriber->lastName = isset($criteria['lastName']) ? $criteria['lastName'] : null;

        $listType = SproutLists::$app->lists->getListType($subscriber->listType);

        return $listType->getListCount($subscriber);
    }

    /**
     * Return total subscriber count on a given list.
     *
     * @param $criteria
     *
     * @return mixed
     * @throws \Exception
     */
    public function getSubscriberCount($criteria)
    {
        $list = new Lists();
        $listTypeParam = isset($criteria['listType']) ? $criteria['listType'] : SproutLists::$defaultSubscriber;
        $list->handle = isset($criteria['listHandle']) ? $criteria['listHandle'] : null;

        $listType = SproutLists::$app->lists->getListType($listTypeParam);
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
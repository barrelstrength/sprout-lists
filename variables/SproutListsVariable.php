<?php

namespace Craft;

class SproutListsVariable
{
	/**
	 * Checks if a user is subscribed to a given list
	 *
	 * @param $criteria
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function getIsSubscribed($criteria)
	{
		$subscription            = new SproutLists_SubscriptionModel();
		$subscription->type      = isset($criteria['type']) ? $criteria['type'] : 'subscriber';
		$subscription->list      = isset($criteria['list']) ? $criteria['list'] : null;
		$subscription->listId    = isset($criteria['listId']) ? $criteria['listId'] : null;
		$subscription->elementId = isset($criteria['elementId']) ? $criteria['elementId'] : null;
		$subscription->userId    = isset($criteria['userId']) ? $criteria['userId'] : null;
		$subscription->email     = isset($criteria['email']) ? $criteria['email'] : null;

		$listType = sproutLists()->lists->getListType($subscription->type);

		return $listType->isSubscribed($subscription);
	}

	// Subscriptions
	// =========================================================================

	/**
	 * Return all subscriptions on a given list
	 *
	 * @param array $criteria
	 *
	 * @return mixed
	 */
	public function getSubscriptions($criteria = array())
	{
		$subscription            = new SproutLists_SubscriptionModel();
		$subscription->type      = isset($criteria['type']) ? $criteria['type'] : 'subscriber';
		$subscription->list      = isset($criteria['list']) ? $criteria['list'] : null;
		$subscription->listId    = isset($criteria['listId']) ? $criteria['listId'] : null;
		$subscription->elementId = isset($criteria['elementId']) ? $criteria['elementId'] : null;
		$subscription->userId    = isset($criteria['userId']) ? $criteria['userId'] : null;
		$subscription->email     = isset($criteria['email']) ? $criteria['email'] : null;

		$listType = sproutLists()->lists->getListType($subscription->type);

		return $listType->getSubscriptions($subscription);
	}

	/**
	 * Return all subscribers on a given list
	 *
	 * @param $criteria
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function getSubscribers($criteria)
	{
		$list         = new SproutLists_ListModel();
		$list->type   = isset($criteria['type']) ? $criteria['type'] : 'subscriber';
		$list->handle = isset($criteria['list']) ? $criteria['list'] : null;

		$listType = sproutLists()->lists->getListType($list->type);

		return $listType->getSubscribers($list);
	}

	// Counts
	// =========================================================================

	/**
	 * Return total subscriber count from given criteria
	 *
	 * @param $criteria
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function getSubscriberCount($criteria)
	{
		$subscription            = new SproutLists_SubscriptionModel();
		$subscription->type      = isset($criteria['type']) ? $criteria['type'] : 'subscriber';
		$subscription->list      = isset($criteria['list']) ? $criteria['list'] : null;
		$subscription->listId    = isset($criteria['listId']) ? $criteria['listId'] : null;
		$subscription->elementId = isset($criteria['elementId']) ? $criteria['elementId'] : null;
		$subscription->userId    = isset($criteria['userId']) ? $criteria['userId'] : null;
		$subscription->email     = isset($criteria['email']) ? $criteria['email'] : null;

		$listType = sproutLists()->lists->getListType($subscription->type);

		return $listType->getSubscriberCount($subscription);
	}

	/**
	 * Return total subscriptions that a given Subscriber has
	 * - How many different lists is a user subscribed to?
	 *
	 * @todo - clarify this, why do we have two methods with different names that accept the same arguments?
	 *
	 * @param $criteria
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function getSubscriptionCount($criteria)
	{
		return $this->getSubscriberCount($criteria);
	}
}
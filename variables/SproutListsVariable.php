<?php

namespace Craft;

class SproutListsVariable
{
	/**
	 * Checks if a user is subscribed to a given list.
	 *
	 * @param $criteria
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function getIsSubscribed($criteria)
	{
		$subscription             = new SproutLists_SubscriptionModel();
		$subscription->type       = isset($criteria['type']) ? $criteria['type'] : 'subscriber';
		$subscription->listHandle = isset($criteria['listHandle']) ? $criteria['listHandle'] : null;
		$subscription->listId     = isset($criteria['listId']) ? $criteria['listId'] : null;
		$subscription->elementId  = isset($criteria['elementId']) ? $criteria['elementId'] : null;
		$subscription->userId     = isset($criteria['userId']) ? $criteria['userId'] : null;
		$subscription->email      = isset($criteria['email']) ? $criteria['email'] : null;

		$listType = sproutLists()->lists->getListType($subscription->type);

		return $listType->isSubscribed($subscription);
	}

	// Subscriptions
	// =========================================================================

	/**
	 * Returns all subscriptions on a given list.
	 *
	 * @param array $criteria
	 *
	 * @return mixed
	 */
	public function getSubscriptions($criteria)
	{
		$subscription             = new SproutLists_SubscriptionModel();
		$subscription->type       = isset($criteria['type']) ? $criteria['type'] : 'subscriber';
		$subscription->listHandle = isset($criteria['listHandle']) ? $criteria['listHandle'] : null;
		$subscription->listId     = isset($criteria['listId']) ? $criteria['listId'] : null;
		$subscription->elementId  = isset($criteria['elementId']) ? $criteria['elementId'] : null;
		$subscription->userId     = isset($criteria['userId']) ? $criteria['userId'] : null;
		$subscription->email      = isset($criteria['email']) ? $criteria['email'] : null;

		$listType = sproutLists()->lists->getListType($subscription->type);

		return $listType->getSubscriptions($subscription);
	}

	/**
	 * Return all subscribers on a given list.
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
		$list->handle = isset($criteria['listHandle']) ? $criteria['listHandle'] : null;

		$listType = sproutLists()->lists->getListType($list->type);

		return $listType->getSubscribers($list);
	}

	// Counts
	// =========================================================================

	/**
	 * Return total subscriptions for a given subscriber or list.
	 *
	 * @param $criteria
	 *
	 * @return int
	 */
	public function getSubscriptionCount($criteria)
	{
		$subscriptions = $this->getSubscriptions($criteria);

		return count($subscriptions);
	}

	/**
	 * Return total subscriber count on a given list.
	 *
	 * @param $criteria
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function getSubscriberCount($criteria)
	{
		$subscribers = $this->getSubscribers($criteria);

		return count($subscribers);
	}
}
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
		$subscription->listType   = isset($criteria['listType']) ? $criteria['listType'] : 'subscriber';
		$subscription->listHandle = isset($criteria['listHandle']) ? $criteria['listHandle'] : null;
		$subscription->listId     = isset($criteria['listId']) ? $criteria['listId'] : null;
		$subscription->elementId  = isset($criteria['elementId']) ? $criteria['elementId'] : null;
		$subscription->userId     = isset($criteria['userId']) ? $criteria['userId'] : null;
		$subscription->email      = isset($criteria['email']) ? $criteria['email'] : null;

		$listType = sproutLists()->lists->getListType($subscription->listType);

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
	public function getLists($criteria = array())
	{
		$subscriber            = new SproutLists_SubscriberModel();
		$subscriber->listType  = isset($criteria['listType']) ? $criteria['listType'] : 'subscriber';
		$subscriber->email     = isset($criteria['email']) ? $criteria['email'] : null;
		$subscriber->userId    = isset($criteria['userId']) ? $criteria['userId'] : null;
		$subscriber->firstName = isset($criteria['firstName']) ? $criteria['firstName'] : null;
		$subscriber->lastName  = isset($criteria['lastName']) ? $criteria['lastName'] : null;

		$listType = sproutLists()->lists->getListType($subscriber->listType);

		return $listType->getLists($subscriber);
	}

	/**
	 * Return all subscribers on a given list.
	 *
	 * @param $criteria
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function getSubscribers($criteria = array())
	{
		$list         = new SproutLists_ListModel();
		$list->type   = isset($criteria['listType']) ? $criteria['listType'] : 'subscriber';
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
	public function getListCount($criteria)
	{
		$lists = $this->getLists($criteria);

		return count($lists);
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
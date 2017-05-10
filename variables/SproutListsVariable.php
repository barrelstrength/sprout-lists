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
		if (!isset($criteria['list']))
		{
			throw new Exception(Craft::t('Missing arguments. list and userId are all required.'));
		}

		$type = SproutLists_SubscriberListType::NAME;

		if (isset($criteria['type']))
		{
			$type = $criteria['type'];
		}

		$listType = sproutLists()->lists->getListType($type);

		return $listType->isSubscribed($criteria);
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
		$type = SproutLists_SubscriberListType::NAME;

		if (isset($criteria['type']))
		{
			$type = $criteria['type'];
		}

		$listType = sproutLists()->lists->getListType($type);

		return $listType->getSubscriptions($criteria);
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
		if (!isset($criteria['list']))
		{
			throw new Exception(Craft::t("Missing arguments. 'list' is required."));
		}

		$type = SproutLists_SubscriberListType::NAME;

		if (isset($criteria['type']))
		{
			$type = $criteria['type'];
		}

		$listType = sproutLists()->lists->getListType($type);

		return $listType->getSubscribers($criteria);
	}

	/**
	 * Return all lists
	 *
	 * @return array
	 */
	public function getLists()
	{
		return sproutLists()->lists->getLists();
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
		$type     = isset($criteria['type']) ? $criteria['type'] : null;
		$listType = sproutLists()->lists->getListType($type);

		return $listType->getSubscriberCount($criteria);
	}

	/**
	 * Return total subscriptions that a given Subscriber has
	 * - How many different lists is a user subscribed to?
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
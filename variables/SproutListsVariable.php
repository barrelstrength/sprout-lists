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

		// If type isn't defined, assume we're working with a default Subscriber list
		$type = isset($criteria['type']) ? $criteria['type'] : 'subscriber';

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
		$type = isset($criteria['type']) ? $criteria['type'] : 'subscriber';

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

		$type     = isset($criteria['type']) ? $criteria['type'] : 'subscriber';
		$listType = sproutLists()->lists->getListType($type);

		/**
		 * @todo - not sure this is working. $criteria gets passed as $listIds to the next method and
		 * has more than just the listIds above.
		 */
		return $listType->getSubscribers($criteria);
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
		$type = isset($criteria['type']) ? $criteria['type'] : 'subscriber';

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
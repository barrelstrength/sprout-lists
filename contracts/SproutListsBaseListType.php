<?php

namespace Craft;

abstract class SproutListsBaseListType extends BaseComponentType
{
	/**
	 * Returns the class name of this List Type
	 *
	 * @return mixed
	 */
	final public function getClassName()
	{
		$class = str_replace('Craft\\', '', get_class($this));

		return $class;
	}

	/**
	 * Subscribe a user to a list for this List Type
	 *
	 * @param $user
	 *
	 * @return mixed
	 */
	abstract public function subscribe($subscription);

	/**
	 * Unsubscribe a user from a list for this List Type
	 *
	 * @param $user
	 *
	 * @return mixed
	 */
	abstract public function unsubscribe($subscription);

	/**
	 * Check if a user is subscribed to a list
	 *
	 * @param $criteria
	 *
	 * @return mixed
	 */
	abstract public function isSubscribed($subscription);

	/**
	 * Return all lists for a given subscriber.
	 *
	 * @param $criteria
	 *
	 * @return mixed
	 */
	abstract public function getLists(SproutLists_SubscriberModel $subscription = null);

	/**
	 * Get subscribers on a given list.
	 *
	 * @param $list
	 *
	 * @return mixed
	 * @internal param $criteria
	 *
	 */
	abstract public function getSubscribers($list);
}
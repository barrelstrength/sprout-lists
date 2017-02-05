<?php

namespace Craft;

abstract class SproutListsBaseListType
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
	 * Returns the name of the List Type
	 *
	 * @return mixed
	 */
	public function getName()
	{
		preg_match("/SproutLists_(.*)ListType/", get_class($this), $matches);

		$name = $matches[1];

		return $name;
	}

	/**
	 * Subscribe a user to a list for this List Type
	 *
	 * @param $user
	 *
	 * @return mixed
	 */
	abstract public function subscribe($criteria);

	/**
	 * Unsubscribe a user from a list for this List Type
	 *
	 * @param $user
	 *
	 * @return mixed
	 */
	abstract public function unsubscribe($criteria);

	/**
	 * Check if a user is subscribed to a list
	 *
	 * @param $criteria
	 *
	 * @return mixed
	 */
	abstract public function isSubscribed($criteria);

	/**
	 * Return all uses subscribed to lists
	 *
	 * @param $criteria
	 *
	 * @return mixed
	 */
	abstract public function getSubscriptions($criteria);

	/**
	 * Get subscribers on a given list
	 *
	 * @param $criteria
	 *
	 * @return mixed
	 */
	abstract public function getSubscribers($criteria);

	/**
	 * Get the subscriber count for a given list
	 *
	 * @param $criteria
	 *
	 * @return mixed
	 */
	abstract public function getSubscriberCount($criteria);
}
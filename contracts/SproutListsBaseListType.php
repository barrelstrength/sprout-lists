<?php

namespace Craft;

abstract class SproutListsBaseListType
{
	final public function getClassName()
	{
		$class = str_replace('Craft\\', '', get_class($this));

		return $class;
	}

	public function getName()
	{
		preg_match("/SproutLists_(.*)ListType/", get_class($this), $matches);

		$name = $matches[1];

		return $name;
	}

	public function getUrl()
	{
		$name = $this->getName();

		return strtolower($name) . 's';
	}

	abstract public function subscribe($user);

	abstract public function unsubscribe($user);

	abstract public function isSubscribed($criteria);

	abstract public function getSubscriptions($criteria);

	abstract public function getSubscribers($criteria);

	abstract public function getListCount($criteria);

	abstract public function getSubscriberCount($criteria);
}
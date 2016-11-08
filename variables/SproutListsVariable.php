<?php
namespace Craft;

class SproutListsVariable
{
	public function getName()
	{
		$plugin = craft()->plugins->getPlugin('sproutlists');

		return $plugin->getName();
	}

	public function getVersion()
	{
		$plugin = craft()->plugins->getPlugin('sproutlists');

		return $plugin->getVersion();
	}

	public function getIsSubscribed($criteria)
	{
		if (!isset($criteria['list']) OR !isset($criteria['elementId']))
		{
			throw new Exception(Craft::t('Missing arguments. list, userId, and elementId are all required.'));
		}

		$type = 'user';

		if (isset($criteria['type']))
		{
			$type = $criteria['type'];
		}

		$listType = sproutLists()->getListType($type);

		return $listType->isSubscribed($criteria);
	}

	// Counts
	// =========================================================================

	public function getSubscriptionCount($criteria)
	{
		if (!isset($criteria['list']))
		{
			throw new Exception(Craft::t("Missing arguments. 'list' is required."));
		}

		$type = 'user';

		if (isset($criteria['type']))
		{
			$type = $criteria['type'];
		}

		$listType = sproutLists()->getListType($type);

		return $listType->getSubscriptionCount($criteria);
	}

	public function getListCount($criteria)
	{
		if (!isset($criteria['list']))
		{
			throw new Exception(Craft::t("Missing arguments. 'list' is required."));
		}

		$type = 'user';

		if (isset($criteria['type']))
		{
			$type = $criteria['type'];
		}

		$listType = sproutLists()->getListType($type);

		return $listType->getListCount($criteria);
	}

	// Subscriptions
	// =========================================================================

	public function getSubscriptions($criteria)
	{
		if (!isset($criteria['list']))
		{
			throw new Exception(Craft::t("Missing arguments. 'list' is required."));
		}

		$type = 'user';

		if (isset($criteria['type']))
		{
			$type = $criteria['type'];
		}

		$listType = sproutLists()->getListType($type);

		return $listType->getSubscriptions($criteria);
	}

	public function getSubscribers($criteria)
	{
		if (!isset($criteria['list']))
		{
			throw new Exception(Craft::t("Missing arguments. 'list' is required."));
		}

		$type = 'user';

		if (isset($criteria['type']))
		{
			$type = $criteria['type'];
		}

		$listType = sproutLists()->getListType($type);

		return $listType->getSubscribers($criteria);
	}
}
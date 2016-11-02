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
		if (!isset($criteria['list']) OR !isset($criteria['userId']) OR !isset($criteria['elementId']))
		{
			throw new Exception(Craft::t('Missing arguments. list, userId, and elementId are all required.'));
		}

		return craft()->sproutLists_subscription->isSubscribed($criteria);
	}

	// Counts
	// =========================================================================

	public function getSubscriptionCount($criteria)
	{
		if (!isset($criteria['list']))
		{
			throw new Exception(Craft::t("Missing arguments. 'list' is required."));
		}

		return craft()->sproutLists_subscription->subscriptionCount($criteria);
	}

	public function getListCount($criteria)
	{
		if (!isset($criteria['list']))
		{
			throw new Exception(Craft::t("Missing arguments. 'list' is required."));
		}

		return craft()->sproutLists_subscription->listCount($criteria);
	}

	// Subscriptions
	// =========================================================================

	public function getSubscriptions($criteria)
	{
		if (!isset($criteria['list']))
		{
			throw new Exception(Craft::t("Missing arguments. 'list' is required."));
		}

		return craft()->sproutLists_subscription->getSubscriptions($criteria);
	}

	public function getSubscribers($criteria)
	{
		if (!isset($criteria['list']))
		{
			throw new Exception(Craft::t("Missing arguments. 'list' is required."));
		}

		return craft()->sproutLists_subscription->getSubscribers($criteria);
	}
}
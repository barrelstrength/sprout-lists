<?php
namespace Craft;

class SproutSubscribeVariable
{
	public function getName()
	{
		$plugin = craft()->plugins->getPlugin('sproutsubscribe');

		return $plugin->getName();
	}

	public function getVersion()
	{
		$plugin = craft()->plugins->getPlugin('sproutsubscribe');

		return $plugin->getVersion();
	}

	public function getIsSubscribed($criteria)
	{
		if (!isset($criteria['list']) OR !isset($criteria['userId']) OR !isset($criteria['elementId']))
		{
			throw new Exception(Craft::t('Missing arguments. list, userId, and elementId are all required.'));
		}

		return craft()->sproutSubscribe_subscription->isSubscribed($criteria);
	}

	// Counts
	// =========================================================================

	public function getSubscriptionCount($criteria)
	{
		if (!isset($criteria['list']))
		{
			throw new Exception(Craft::t("Missing arguments. 'list' is required."));
		}

		return craft()->sproutSubscribe_subscription->subscriptionCount($criteria);
	}

	public function getSubscriberCount($criteria)
	{
		if (!isset($criteria['list']))
		{
			throw new Exception(Craft::t("Missing arguments. 'list' is required."));
		}

		return craft()->sproutSubscribe_subscription->subscriberCount($criteria);
	}

	// Subscriptions
	// =========================================================================

	public function getSubscriptions($criteria)
	{
		if (!isset($criteria['list']))
		{
			throw new Exception(Craft::t("Missing arguments. 'list' is required."));
		}

		return craft()->sproutSubscribe_subscription->getSubscriptions($criteria);
	}

	public function getSubscribers($criteria)
	{
		if (!isset($criteria['list']))
		{
			throw new Exception(Craft::t("Missing arguments. 'list' is required."));
		}

		return craft()->sproutSubscribe_subscription->getSubscribers($criteria);
	}
}
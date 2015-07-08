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

	public function getUserData($list)
	{
		return craft()->sproutSubscribe_subscription->userData($list);
	}

	// Counts
	// =========================================================================

	public function getSubscriptionCount($list, $userId = null)
	{
		return craft()->sproutSubscribe_subscription->subscriptionCount($list, $userId);
	}

	public function getTotalSubscriptions($elementId)
	{
		return craft()->sproutSubscribe_subscription->totalSubscriptions($elementId);
	}

	// Subscriptions
	// =========================================================================

	public function getSubscriptions($list, $userId = null)
	{
		return craft()->sproutSubscribe_subscription->getSubscriptions($list, $userId);
	}

	public function getSubscribers($list, $elementId = null)
	{
		return craft()->sproutSubscribe_subscription->getSubscribers($list, $elementId);
	}

	public function getPopularSubscriptions($limit)
	{
		return craft()->sproutSubscribe_subscription->popularSubscriptions($limit);
	}
}
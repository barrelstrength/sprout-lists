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
		return craft()->sproutSubscribe_subscription->isSubscribed($criteria);
	}

	public function getSubscriptionCount($key, $userId)
	{
		return craft()->sproutSubscribe_subscription->subcriptionCount($key, $userId);
	}

	public function getElementIds($key, $userId)
	{
		return craft()->sproutSubscribe_subscription->elementIds($key, $userId);
	}

	public function getUserIds($key, $elementId)
	{
		return craft()->sproutSubscribe_subscription->userIds($key, $elementId);
	}

	public function getTotalSubscriptions($elementId)
	{
		return craft()->sproutSubscribe_subscription->totalSubscriptions($elementId);
	}

	public function getUserData($key)
	{
		return craft()->sproutSubscribe_subscription->userData($key);
	}

	public function getPopularSubscriptions($limit)
	{
		return craft()->sproutSubscribe_subscription->popularSubscriptions($limit);
	}
  
}

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

  public function getSubsciptionCount($key, $userId = null)
  {
    return craft()->sproutSubscribe_subscription->subcriptionCount($key, $userId = null);
  }

  public function getElementIds($key, $userId = null)
  {
    return craft()->sproutSubscribe_subscription->elementIds($key, $usertId = null);
  }

  public function getUserIds($key, $elementId = null)
  {
    return craft()->sproutSubscribe_subscription->userIds($key, $userId = null);
  }

  public function getTotalSubscriptions()
  {
    return craft()->sproutSubscribe_subscription->totalSubscriptions();
  }
  
}

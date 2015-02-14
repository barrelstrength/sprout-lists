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

  public function isSubscribed($userId, $elementId)
  {
    return craft()->sproutSubscribe->isSubscribed($userId, $elementId);
  }

  public function subscriptionIds($userId = null, $elementType = 'Entry', $criteria = array())
  {
    return craft()->sproutSubscribe->subscriptionIds($userId, $elementType, $criteria);
  }
}

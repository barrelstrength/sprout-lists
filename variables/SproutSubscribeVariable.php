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

  // @TODO - move logic to service layer
  public function isSubscribed($userId = null, $elementId = null)
  {
    if (!$userId or !$elementId)
    {
      return false;
    }

    $query = craft()->db->createCommand()
            ->select('userId, elementId')
            ->from('sproutsubscribe_subscriptions')
            ->where(array(
              'AND', 
              'userId = :userId', 
              'elementId = :elementId'
            ), array(
              ':userId' => $userId, 
              ':elementId' => $elementId
            ))->queryRow();
    
    return (is_array($query)) ? true : false;
  }

  // @TODO - move logic to service layer
  // @TODO - should this be abstracted to any other use cases?
  public function subscriptionIds($userId = null, $elementType = 'Entry', $criteria = array())
  {
    $userId = craft()->userSession->id;
    
    if (!$userId)
    {
      return false;
    }

    // join the sproutsubscribe_subscriptions and elements table to make sure we're only
    // getting back the IDs of the Elements that match our type.

    $results = craft()->db->createCommand()
            ->select('elementId')
            ->from('sproutsubscribe_subscriptions')
            ->where('userId = :userId', array(
              ':userId' => $userId
            ))->queryAll();

    $ids = "";

    foreach ($results as $key => $value) 
    {
      if ($ids == "") 
      {
        $ids = $value['elementId'];
      }
      else
      { 
        $ids .= "," . $value['elementId'];
      }
    }

    return $ids;

  }

  
}

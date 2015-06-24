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
  public function isSubscribed($criteria)
  {

    if (!$criteria['userId'] or !$criteria['elementId'])
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
              ':userId' => $criteria['userId'], 
              ':elementId' => $criteria['elementId']
            ))->queryRow();
    
    return (is_array($query)) ? true : false;
  }

  // @TODO - move logic to service layer
  // @TODO - should this be abstracted to any other use cases?
  /*public function subscriptionIds($userId = null, $elementType = 'Entry', $criteria = array())
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

  // Subscription list count
  /*public function subscriptionCount($key)
  {
    // Find key else create key entry
    $query = craft()->db->createCommand()
      ->select('id')
      ->from('sproutsubscribe_lists')
      ->where(array(
        'key = :key'
      ), array(
        ':key' => $key
      ))->queryAll();


    if(!empty($query))
    {

      $listsId = $query[0]["id"];
      // Find key else create key entry
      $query = craft()->db->createCommand()
        ->select('count(listsId) as count')
        ->from('sproutsubscribe_subscriptions')
        ->where(array(
          'listsId = :listsId'
        ), array(
          ':listsId' => $listsId
        ))->queryAll();

        return $query[0]["count"];
    } else {
      return 0;
    }

  }

  public function userSubscriptionsIds()
  {
    $userId = craft()->userSession->id;

    // Find key else create key entry
    $query = craft()->db->createCommand()
      ->select('listsId, elementId')
      ->from('sproutsubscribe_subscriptions')
      ->where(array(
        'key = :key'
      ), array(
        ':key' => $key
      ))->queryAll();
  }*/



public function subscriptionCount($key, $userId = null)
{
    $listsid = 0;

    // Find key else create key entry
    $query = craft()->db->createCommand()
      ->select('id')
      ->from('sproutsubscribe_lists')
      ->where(array(
        'key = :key'
      ), array(
        ':key' => $key
      ))->queryAll();

    // Set KeyID
    if(!empty($query))
    {
        $listsId = $query[0]["id"];
    } else {
        return $listsId;
    }

    if($userId == null)
    {

        // Find key else create key entry
        $query = craft()->db->createCommand()
             ->select('count(listsId) as count')
            ->from('sproutsubscribe_subscriptions')
            ->where(array(
                'listsId = :listsId'
            ), array(
                ':listsId' => $listsId
            ))->queryAll();

            return $query[0]["count"];


    // If userID is not null
    } else {
        if(!is_array($userId))
        {
            $userArray = array();
            array_push($userArray, $userId); 
            $userId = $userArray;
        }

        // Find key else create key entry
        $query = craft()->db->createCommand()
             ->select('count(listsId) as count')
            ->from('sproutsubscribe_subscriptions')
            ->where(array('and', 'listsId = :listsId', array('in', 'userId', $userId)))
            ->where(array(
                'listsId = :listsId'
            ), array(
                ':listsId' => $listsId
            ))->queryAll();

            return $query[0]["count"];

    }

}

public function subscriptionIds($key, $elementId = null)
{
    $listsid = 0;

    // Find key else create key entry
    $query = craft()->db->createCommand()
      ->select('id')
      ->from('sproutsubscribe_lists')
      ->where(array(
        'key = :key'
      ), array(
        ':key' => $key
      ))->queryAll();

    // Set KeyID
    if(!empty($query))
    {
        $listsId = $query[0]["id"];
    } else {
        return $listsId;
    }

    if($elementId == null)
    {

        // Find key else create key entry
        $query = craft()->db->createCommand()
             ->select('elementId')
            ->from('sproutsubscribe_subscriptions')
            ->where(array(
                'listsId = :listsId'
            ), array(
                ':listsId' => $listsId
            ))->queryAll();

            //return $query[0]["count"];

            var_dump($query);


    // If userID is not null
    } else {
        if(!is_array($elementId))
        {
            $elementsArray = array();
            array_push($elementsArray, $elementId); 
            $elementId = $elementsArray;
        }

        // Find key else create key entry
        $query = craft()->db->createCommand()
             ->select('elementId')
            ->from('sproutsubscribe_subscriptions')
            ->where(array('and', 'listsId = :listsId', array('in', 'elementId', $elementId)))
            ->where(array(
                'listsId = :listsId'
            ), array(
                ':listsId' => $listsId
            ))->queryAll();

            var_dump($query);
            //return $query[0]["count"];

    }

}


  
}

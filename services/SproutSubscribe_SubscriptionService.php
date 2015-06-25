<?php
namespace Craft;

class SproutSubscribe_SubscriptionService extends BaseApplicationComponent
{

	public function isSubscribed($criteria)
	{
		if (!isset($criteria['elementId']) OR !isset($criteria['key']))
	    {
	    	return false;
	    }

	    $query = craft()->db->createCommand()
	        ->select('userId, elementId')
	        ->from('sproutsubscribe_subscriptions')
	        ->where(array(
	            'AND', 
	            'userId = :userId', 
	            'elementId = :elementId',
	            'listsId = :listsId'
	        ), array(
	            ':userId' => craft()->userSession->id, 
	            ':elementId' => $criteria['elementId'],
	            ':listsId' => $this->getKeyId($criteria['key'])
	        ))->queryRow();
	    
	    return (is_array($query)) ? true : false;
	}

	public function subcriptionCount($key, $userId = null)
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

	    // Set KeyID
	    if(!empty($query))
	    {
	       	$listsId = $query[0]["id"];
	    } else {
	      	return false;
	    }

	    // If no user id's provided
	    // return count for current handle
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

	public function elementIds($key, $userId = null)
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

	    // Set KeyID
	    if(!empty($query))
	    {
	        $listsId = $query[0]["id"];
	    } else {
	        return false;
	    }

	    if($userId == null)
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

	            return $query;

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
	             ->select('elementId')
	            ->from('sproutsubscribe_subscriptions')
	            ->where(array('and', 'listsId = :listsId', array('in', 'userId', $userId)))
	            ->where(array(
	                'listsId = :listsId'
	            ), array(
	                ':listsId' => $listsId
	            ))->queryAll();

	            return $query;

	    }
	}

	public function userIds($key, $elementId = null)
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

	    // Set KeyID
	    if(!empty($query))
	    {
	        $listsId = $query[0]["id"];
	    } else {
	        return false;
	    }

	    if($elementId == null)
	    {

	        // Find key else create key entry
	        $query = craft()->db->createCommand()
	             ->select('userId')
	            ->from('sproutsubscribe_subscriptions')
	            ->where(array(
	                'listsId = :listsId'
	            ), array(
	                ':listsId' => $listsId
	            ))->queryAll();

	            return $query;

	    // If userID is not null
	    } else {
	        if(!is_array($elementId))
	        {
	            $elementArray = array();
	            array_push($elementArray, $elementId); 
	            $elementId = $elementArray;
	        }

	        // Find key else create key entry
	        $query = craft()->db->createCommand()
	             ->select('userId')
	            ->from('sproutsubscribe_subscriptions')
	            ->where(array('and', 'listsId = :listsId', array('in', 'elementId', $elementId)))
	            ->where(array(
	                'listsId = :listsId'
	            ), array(
	                ':listsId' => $listsId
	            ))->queryAll();

	            return $query;

	    }
	}

	public function totalSubscriptions()
	{
		// Find key else create key entry
	    $query = craft()->db->createCommand()
	        ->select('count(userId) as count')
            ->from('sproutsubscribe_subscriptions')
	        ->queryAll();

	    return $query[0]["count"];
	}

	// Retrieve Key unique id
	public function getKeyId($name)
  	{
  		// Create camel case version of string
	    $handle = $this->camelCase($name);

	    // Find key else create key entry
	    $query = craft()->db->createCommand()
	      ->select('id')
	      ->from('sproutsubscribe_lists')
	      ->where(array(
	      	'AND', 
	        'name = :name',
	        'handle = :handle'
	      ), array(
	      	':name' => $name,
	        ':handle' => $handle
	      ))->queryAll();

	    if(!empty($query))
	    {
	    	// return found key
	      	return $query[0]["id"];
	    } else {
	    	// If no key found dynamically create one
	      	$record = new SproutSubscribe_ListsRecord;
	      	$record->name = $name;
	      	$record->handle = $handle;

	      	$record->save();

	      	return $record->id;
	    }
  	}

  	// Strips foriegn characters
  	// Camel cases string provided
	private static function camelCase($str, array $noStrip = [])
	{
	    // non-alpha and non-numeric characters become spaces
	    $str = preg_replace('/[^a-z0-9' . implode("", $noStrip) . ']+/i', ' ', $str);
	    $str = trim($str);
	    
	    // uppercase the first character of each word
	    $str = ucwords($str);
	    $str = str_replace(" ", "", $str);
	    $str = lcfirst($str);
	   
	    return $str;
	}
	
}
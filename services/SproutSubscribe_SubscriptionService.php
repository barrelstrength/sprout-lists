<?php
namespace Craft;

class SproutSubscribe_SubscriptionService extends BaseApplicationComponent
{
	/**
	 * Check to see if a user is already subscribed
	 * @param  Array  $criteria  Array of element info
	 * @return boolean           bool true == isSubscribed
	 *                           bool false == Not subscribed
	 */
	public function isSubscribed($criteria)
	{
		if (!isset($criteria['elementId']) OR !isset($criteria['key']) OR !isset($criteria['userId']))
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
				':userId' => $criteria['userId'], 
				':elementId' => $criteria['elementId'],
				':listsId' => $this->getKeyId($criteria['key'])
			))->queryRow();
		
		return (is_array($query)) ? true : false;
	}

	/**
	 * Creates a new subscription
	 * @param  String $key 	String representing subscription category.
	 * @return Bool       	Status True/False
	 */
	public function newSubscription($userId, $elementId, $key)
	{
		$listsId = $this->getKeyId($key);

		$record = new SproutSubscribe_SubscriptionRecord;
		$record->userId = $userId;
		$record->elementId = $elementId;
		$record->listsId = $listsId;


		if($record->save())
		{
			return true;
		}

		return false;
	}

	/**
	 * Creates a new subscription
	 * @param  String $key 	String representing subscription category.
	 * @return Bool       	Status True/False
	 */
	public function unsubscribe($userId, $elementId, $key)
	{
		$listsId = $this->getKeyId($key);

		$result = craft()->db->createCommand()
			->delete('sproutsubscribe_subscriptions', array(
				'userId' => $userId,
				'elementId' => $elementId,
				'listsId' => $keyId
			));

		if($result)
		{
			return true;
		}

		return false;

	}


	/**
	 * Retrieve subscription count based on key/userIds
	 * @param  String $key    		String representing subscription category.
	 * @param  Int/Array $userId 	Int or Array of Ints for User Ids.
	 * @return Array         		Subscription Count.
	 */
	public function subcriptionCount($key, $userId = null)
	{
		// Get key Id
		$listsId = $this->getKeyId($key);

		// If no user id's provided
		// return count for current handle
		if($userId == null)
		{
			$count = craft()->db->createCommand()
				->select('count(listsId) as count')
				->from('sproutsubscribe_subscriptions')
				->where(array(
					'listsId = :listsId'
				), array(
					':listsId' => $listsId
				))->queryScalar();

			return $count;

		// If userID is not null
		} 
		else 
		{
			if(!is_array($userId))
			{
				ArrayHelper::stringToArray($userId);
			}

			// Find key else create key entry
			$count = craft()->db->createCommand()
				->select('count(listsId) as count')
				->from('sproutsubscribe_subscriptions')
				->where(array('and', 'listsId = :listsId', array('in', 'userId', $userId)))
				->where(array(
					'listsId = :listsId'
				), array(
					':listsId' => $listsId
				))->queryScalar();

			return $count;
		}
	}

	/**
	 * Retrieve element ids based on element Ids 
	 * @param  String $key    String representing subscription category.
	 * @param  Int $userId    Int or Array of Ints for User Ids.
	 * @return Array          Int or Array of Ints of element Ids.
	 */
	public function elementIds($key, $userId = null)
	{

		// Get key Id
		$listsId = $this->getKeyId($key); 

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
		} 
		else 
		{
			if(!is_array($userId))
			{
				ArrayHelper::stringToArray($userId);
			}

			// Find key else create key entry
			$query = craft()->db->createCommand()
				->select('userId')
				->from('sproutsubscribe_subscriptions')
				->where(array('and', "listsId = $listsId", array('in', 'userId', $userId)))
				->queryAll();
			// @TODO Check this is returning correct data
			return $query;

		}
	}

	/**
	 * Retrieve userIds by elementId & Key
	 * @param  String $key        String representing subscription category.
	 * @param  Int $elementId     Int or Array of Ints for Elements.
	 * @return Array              Int or Array of Ints of User Ids.
	 */
	public function userIds($key, $elementId = null)
	{

		// Get key Id
		$listsId = $this->getKeyId($key);

		// @TODO simplify queries in all functions 2x
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
		} 
		else 
		{
			if(!is_array($elementId))
			{
				ArrayHelper::stringToArray($elementId);
			}

			// Find key else create key entry
			$query = craft()->db->createCommand()
				->select('userId')
				->from('sproutsubscribe_subscriptions')
				->where(array('and', "listsId = $listsId", array('in', 'elementId', $elementId)))
				->queryAll();

			return $query;

		}
	}

	/**
	 * Retrieve id of "key" from lists table.
	 * @param  string $name Takes key converts to camel case,
	 *                      Queries to check if it exists.
	 *                      If not dynamically creates it.
	 * @return int          Returns id of existing or dynamic key.
	 */
	public function getKeyId($name)
	{
		// Create camel case version of string
		$handle = $this->camelCase($name);

		// Find key else create key entry
		$listId = craft()->db->createCommand()
		  ->select('id')
		  ->from('sproutsubscribe_lists')
		  ->where(array(
			'AND', 
			'name = :name',
			'handle = :handle'
		  ), array(
			':name' => $name,
			':handle' => $handle
		  ))->queryScalar();

		if(!$listId)
		{
			// If no key found dynamically create one
			$record = new SproutSubscribe_ListsRecord;
			$record->name = $name;
			$record->handle = $handle;

			$record->save();

			return $record->id;
		}

		return $listId;
	}

	/**
	 * Get total count of subscriptions on an element.
	 * @param  Int $elementId    Id of Element.
	 * @return Int            	 Subscription count.
	 */
	public function totalSubscriptions($elementId = null)
	{

		if($elementId == null)
		{
			// Find key else create key entry
			$query = craft()->db->createCommand()
				->select('count(userId) as count')
				->from('sproutsubscribe_subscriptions')
				->queryScalar();

			return $query;
		} else {

			$query = craft()->db->createCommand()
				->select('elementId')
				->from('sproutsubscribe_subscriptions')
				->where("elementId= $elementId")
				->query();

			return $query;

		}
	}


	/**
	 * Query to retrieve elementIds and DateCreated.
	 * @param  string $key String representing subscription category.
	 * @return array       Array contains returned elementIds and Dates.
	 */
	public function userData($key)
	{
		$userId = craft()->userSession->id;
		// Get key Id
		$listsId = $this->getKeyId($key);

		$query = craft()->db->createCommand()
		  ->select('elementId, dateCreated')
		  ->from('sproutsubscribe_subscriptions')
		  ->where(array(
			'AND', 
			'listsId = :listsId',
			'userId = :userId'
		  ), array(
			':listsId' => $listsId,
			':userId' => $userId
		  ))->queryAll();


		return $query;
	}

	/**
	 * Reutnrs top subscripts limit is defined
	 * @param  Int $limit   Number of wanted returns
	 * @return Array        Returned Elements
	 */
	public function popularSubscriptions($limit)
	{
		$query = craft()->db->createCommand("SELECT elementId, COUNT(elementId) AS count FROM craft_sproutsubscribe_subscriptions GROUP BY elementId ORDER BY count DESC LIMIT $limit")
				->query();

		return $query;
	}

	/**
	 * Returns camelCased version of original string.
	 * @param  string $str     String to camel case.
	 * @param  array  $noStrip Characters to strip (optional).
	 * @return string          Camel cased string.
	 */
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
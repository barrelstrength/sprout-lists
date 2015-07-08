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

	/**
	 * Retrieve subscription count based on key/userIds
	 * @param  String $key    		String representing subscription category.
	 * @param  Int/Array $userId 	Int or Array of Ints for User Ids.
	 * @return Array         		Subscription Count.
	 */
	public function subcriptionCount($key, $userId = null)
	{

		$listsId = craft()->db->createCommand()
            ->select('id')
            ->from('sproutsubscribe_lists')
            ->where(array(
            	'key = :key'
            ), array(
               ':key' => $key
            ))->queryScalar();

		if($listsId == false)
		{
			return false;
		}

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

		$listsId = craft()->db->createCommand()
            ->select('id')
            ->from('sproutsubscribe_lists')
            ->where(array(
            	'key = :key'
            ), array(
               ':key' => $key
            ))->queryScalar();


		// Set KeyID
		if($listsId == false)
		{
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
		} 
		else 
		{
			die("you are here");
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

			//return ArrayHelper::flattenArray($query);

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

		$listsId = craft()->db->createCommand()
            ->select('id')
            ->from('sproutsubscribe_lists')
            ->where(array(
            	'key = :key'
            ), array(
               ':key' => $key
            ))->queryScalar();

		if($listsId == false)
		{
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
	 * Query to retrieve elementIds and DateCreated.
	 * @param  string $key String representing subscription category.
	 * @return array       Array contains returned elementIds and Dates.
	 */
	public function userData($key)
	{
		$userId = craft()->userSession->id;

		// Get the lists Id
		$listsId = craft()->db->createCommand()
            ->select('id')
            ->from('sproutsubscribe_lists')
            ->where(array(
            	'key = :key'
            ), array(
               ':key' => $key
            ))->queryScalar();

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
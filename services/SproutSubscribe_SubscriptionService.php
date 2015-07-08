<?php
namespace Craft;

class SproutSubscribe_SubscriptionService extends BaseApplicationComponent
{
	/**
	 * Subscribes a user to an element
	 * @param  String $list String representing subscription grouping
	 * @return Bool       	Status True/False
	 */
	public function subscribe($list, $userId, $elementId)
	{
		$listId = $this->getListId($list);

		$record = new SproutSubscribe_SubscriptionRecord;
		$record->listId = $listId;
		$record->userId = $userId;
		$record->elementId = $elementId;

		if($record->save())
		{
			return true;
		}

		return false;
	}

	/**
	 * Unsubscribes a user from an element
	 * @param  String $list String representing subscription category.
	 * @return Bool       	Status True/False
	 */
	public function unsubscribe($list, $userId, $elementId)
	{
		$listId = $this->getListId($list);

		$result = craft()->db->createCommand()
			->delete('sproutsubscribe_subscriptions', array(
				'listId' => $listId,
				'userId' => $userId,
				'elementId' => $elementId,
			));

		if($result)
		{
			return true;
		}

		return false;
	}

	/**
	 * Check to see if a user is already subscribed
	 * @param  Array  $criteria  Array of element info
	 * @return boolean           bool true == isSubscribed
	 *                           bool false == Not subscribed
	 */
	public function isSubscribed($criteria)
	{
		$isSubscribed = craft()->db->createCommand()
			->select('userId, elementId')
			->from('sproutsubscribe_subscriptions')
			->where(array(
				'AND',
				'listId = :listId',
				'userId = :userId', 
				'elementId = :elementId',
			), array(
				':listId' => $this->getListId($criteria['list']),
				':userId' => $criteria['userId'], 
				':elementId' => $criteria['elementId'],
			))->queryScalar();

		return ($isSubscribed) ? true : false;
	}

	/**
	 * Retrieve subscription count based on list/userIds
	 * @param  String $list    		String representing subscription category.
	 * @param  Int/Array $userId 	Int or Array of Ints for User Ids.
	 * @return Array         		Subscription Count.
	 */
	public function subscriptionCount($list, $userId = null)
	{
		$listId = $this->getListId($list);

		// If no user id's provided return count for current handle
		if($userId == null)
		{
			$count = craft()->db->createCommand()
				->select('count(listId) as count')
				->from('sproutsubscribe_subscriptions')
				->where(array(
					'listId = :listId'
				), array(
					':listId' => $listId
				))->queryScalar();

			return $count;
		} 
		else 
		{
			$userId = $this->prepareIdsForQuery($userId);

			$count = craft()->db->createCommand()
				->select('count(listId) as count')
				->from('sproutsubscribe_subscriptions')
				->where(array('and', 'listId = :listId', array('in', 'userId', $userId)))
				->where(array(
					'listId = :listId'
				), array(
					':listId' => $listId
				))->queryScalar();

			return $count;
		}
	}

	/**
	 * Retrieve element ids based on user ids
	 * @param  String $list   String representing subscription category.
	 * @param  Int $userId    Int or Array of Ints for User Ids.
	 * @return Array          Int or Array of Ints of element Ids.
	 */
	public function getSubscriptions($list, $userId = null)
	{
		$listId = $this->getListId($list);
		$subscriptions = null;

		if($userId == null)
		{
			$subscriptions = craft()->db->createCommand()
				->select('userId, elementId, dateCreated, COUNT(elementId) AS count')
				->from('sproutsubscribe_subscriptions')
				->where(array('listId = :listId'), array(':listId' => $listId))
				->queryAll();
		} 
		else 
		{
			$userId = $this->prepareIdsForQuery($userId);

			$subscriptions = craft()->db->createCommand()
				->select('userId, elementId, dateCreated, COUNT(elementId) AS count')
				->from('sproutsubscribe_subscriptions')
				->where(array('and', "listId = $listId", array('in', 'userId', $userId)))
				->queryAll();
		}

		$subscriptionModels = SproutSubscribe_SubscriptionModel::populateModels($subscriptions, 'elementId');

		return $subscriptionModels;
	}

	/**
	 * Retrieve userIds by elementId & List
	 * @param  String $list       String representing subscription category.
	 * @param  Int $elementId     Int or Array of Ints for Elements.
	 * @return Array              Int or Array of Ints of User Ids.
	 */
	public function getSubscribers($list, $elementId = null)
	{
		$listId = $this->getListId($list);
		$subscriptions = null;

		if($elementId == null)
		{
			$subscriptions = craft()->db->createCommand()
				->select('userId')
				->from('sproutsubscribe_subscriptions')
				->where(array('listId = :listId'), array(':listId' => $listId))
				->queryAll();
		} 
		else 
		{
			$elementId = $this->prepareIdsForQuery($elementId);

			$subscriptions = craft()->db->createCommand()
				->select('userId')
				->from('sproutsubscribe_subscriptions')
				->where(array('and', "listId = $listId", array('in', 'elementId', $elementId)))
				->queryAll();
		}

		$subscriptionModels = SproutSubscribe_SubscriptionModel::populateModels($subscriptions, 'userId');

		return $subscriptionModels;
	}

	/**
	 * Retrieve id of "list" from lists table.
	 * @param  string $name Takes list converts to camel case,
	 *                      Queries to check if it exists.
	 *                      If not dynamically creates it.
	 * @return int          Returns id of existing or dynamic list.
	 */
	public function getListId($name)
	{
		$handle = $this->camelCase($name);

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

		// If no key found dynamically create one
		if(!$listId)
		{
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
			$query = craft()->db->createCommand()
				->select('COUNT(userId) as count')
				->from('sproutsubscribe_subscriptions')
				->queryScalar();

			return $query;
		}
		else
		{
			$query = craft()->db->createCommand()
				->select('COUNT(elementId) as count')
				->from('sproutsubscribe_subscriptions')
				->where("elementId= $elementId")
				->queryScalar();

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
		$subscriptions = craft()->db->createCommand()
			->select('elementId, COUNT(elementId) AS count')
			->from('sproutsubscribe_subscriptions')
			->group('elementId')
			->order('count DESC')
			->limit($limit)
			->queryAll();

		$subscriptionModels = SproutSubscribe_SubscriptionModel::populateModels($subscriptions, 'elementId');

		return $subscriptionModels;
	}

	/**
	 * Query to retrieve elementIds and DateCreated.
	 * @param  string $list String representing subscription category.
	 * @return array       Array contains returned elementIds and Dates.
	 */
	public function userData($list)
	{
		$userId = craft()->userSession->id;
		$listId = $this->getListId($list);

		$query = craft()->db->createCommand()
			->select('elementId, dateCreated')
			->from('sproutsubscribe_subscriptions')
			->where(array(
				'AND',
				'listId = :listId',
				'userId = :userId'
			), array(
				':listId' => $listId,
				':userId' => $userId
			))->queryAll();

		return $query;
	}

	/**
	 * @param $userId
	 */
	public function prepareIdsForQuery($ids)
	{
		if (!is_array($ids))
		{
			return ArrayHelper::stringToArray($ids);
		}

		return $ids;
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
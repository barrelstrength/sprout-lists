<?php
namespace Craft;

class SproutLists_SubscriptionService extends BaseApplicationComponent
{
	/**
	 * Subscribes a user to an element
	 * @param  String $list String representing subscription grouping
	 * @return Bool       	Status True/False
	 */
	public function subscribe(SproutLists_SubscriptionModel $subscription)
	{
		$listId = $this->getListId($subscription->list);

		$record = new SproutLists_SubscriptionRecord;
		$record->listId = $listId;
		$record->userId = $subscription->userId;
		$record->elementId = $subscription->elementId;

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
	public function unsubscribe(SproutLists_SubscriptionModel $subscription)
	{
		$listId = $this->getListId($subscription->list);

		$result = craft()->db->createCommand()
			->delete('sproutlists_subscriptions', array(
				'listId' => $listId,
				'userId' => $subscription->userId,
				'elementId' => $subscription->elementId,
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
			->from('sproutlists_subscriptions')
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
	 * Retrieve element ids based on user ids
	 * @param  String $list   String representing subscription category.
	 * @param  Int $userId    Int or Array of Ints for User Ids.
	 * @return Array          Int or Array of Ints of element Ids.
	 */
	public function getSubscriptions($criteria)
	{
		$listId = $this->getListId($criteria['list']);

		$query = craft()->db->createCommand()
			->select('userId, elementId, dateCreated, COUNT(elementId) AS count')
			->from('sproutlists_subscriptions')
			->group('elementId');

		if (isset($criteria['userId']))
		{
			// Search by user ID or array of user IDs
			$userIds = $this->prepareIdsForQuery($criteria['userId']);

			$query->where(array('and', "listId = $listId", array('in', 'userId', $userIds)));
		}
		else
		{
			$query->where(array('listId = :listId'), array(':listId' => $listId));
		}

		if (isset($criteria['order']))
		{
			$query->order($criteria['order']);
		}

		if (isset($criteria['limit']))
		{
			$query->limit($criteria['limit']);
		}

		$subscriptions = $query->queryAll();

		$subscriptionModels = SproutLists_SubscriptionModel::populateModels($subscriptions, 'elementId');

		return $subscriptionModels;
	}

	/**
	 * Retrieve userIds by elementId & List
	 * @param  String $list       String representing subscription category.
	 * @param  Int $elementId     Int or Array of Ints for Elements.
	 * @return Array              Int or Array of Ints of User Ids.
	 */
	public function getSubscribers($criteria)
	{
		$listId = $this->getListId($criteria['list']);

		$query = craft()->db->createCommand()
			->select('userId')
			->from('sproutlists_subscriptions')
			->where(array('listId = :listId'), array(':listId' => $listId));

		if (isset($criteria['elementId']))
		{
			$elementId = $this->prepareIdsForQuery($criteria['elementId']);
			$query->andWhere(array('in', 'elementId', $elementId));
		}
		else
		{
			$query->group('userId');
		}

		if (isset($criteria['limit']))
		{
			$query->limit($criteria['limit']);
		}

		$subscriptions = $query->queryAll();

		$subscriptionModels = SproutLists_SubscriptionModel::populateModels($subscriptions);

		return $subscriptionModels;
	}

	/**
	 * Retrieve subscription count based on list/userIds
	 * @param  String $list    		String representing subscription category.
	 * @param  Int/Array $userId 	Int or Array of Ints for User Ids.
	 * @return Array         		Subscription Count.
	 */
	public function listCount($criteria)
	{
		$listId = $this->getListId($criteria['list']);

		$query = craft()->db->createCommand()
			->select('count(listId) as count')
			->from('sproutlists_subscriptions')
			->where("listId = $listId");

		if(isset($criteria['userId']))
		{
			$userId = $this->prepareIdsForQuery($criteria['userId']);

			$query->where(array('and', 'listId = :listId', array('in', 'userId', $userId)));
			$query->group('userId');
		}

		$count = $query->queryScalar();

		return $count;
	}

	/**
	 * Get total count of subscribers to an element.
	 * @param  Int $elementId    Id of Element.
	 * @return Int            	 Subscription count.
	 */
	public function subscriberCount($criteria)
	{
		$listId = $this->getListId($criteria['list']);

		$query = craft()->db->createCommand()
			->select('count(listId) as count')
			->from('sproutlists_subscriptions')
			->where(array('listId = :listId'), array(':listId' => $listId));

		if(isset($criteria['elementId']))
		{
			$elementId = $this->prepareIdsForQuery($criteria['elementId']);

			$query->andWhere(array('in', 'elementId', $elementId));
		}
		else
		{
			$query->group('userId');
		}

		$count = $query->queryScalar();

		return $count;
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
			->from('sproutlists_lists')
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
			$record = new SproutLists_ListsRecord;
			$record->name = $name;
			$record->handle = $handle;

			$record->save();

			return $record->id;
		}

		return $listId;
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
<?php
namespace Craft;

class SproutLists_UserService extends BaseApplicationComponent
{
	/**
	 * Subscribes a user to an element
	 * @param  String $list String representing subscription grouping
	 * @return Bool       	Status True/False
	 */
	public function subscribe(SproutLists_UserRecipientModel $model)
	{
		$record = new SproutLists_UserRecipientRecord;

		$modelAttributes = $model->getAttributes();

		if (!empty($modelAttributes))
		{
			foreach ($modelAttributes as $handle => $value)
			{
				$record->setAttribute($handle, $value);
			}
		}

		$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;

		if ($record->validate())
		{
			try
			{
				if (craft()->elements->saveElement($model))
				{
					$record->id = $model->id;

					if ($record->save(false))
					{
						if ($transaction && $transaction->active)
						{
							$transaction->commit();
						}

						return true;
					}
				}

			}
			catch (\Exception $e)
			{
				if ($transaction && $transaction->active)
				{
					$transaction->rollback();
				}

				throw $e;
			}
		}



		return false;
	}

	/**
	 * Unsubscribes a user from an element
	 * @param  String $list String representing subscription category.
	 * @return Bool       	Status True/False
	 */
	public function unsubscribe(SproutLists_UserRecipientModel $user)
	{
		$listId = $user->listId;

		$result = craft()->db->createCommand()
			->delete('sproutlists_users', array(
				'listId' => $listId,
				'userId' => $user->userId,
				'elementId' => $user->elementId,
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
		$query = craft()->db->createCommand()
			->select('userId, elementId')
			->from('sproutlists_users')
			->where(array(
				'AND',
				'listId = :listId',
				'userId = :userId',
				'elementId = :elementId',
			), array(
				':listId' => sproutLists()->getListId($criteria['list']),
				':userId' => $criteria['userId'],
				':elementId' => $criteria['elementId'],
			));

		$isSubscribed = $query->queryScalar();

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
		$listId = sproutLists()->getListId($criteria['list']);

		$query = craft()->db->createCommand()
			->select('userId, elementId, dateCreated, COUNT(elementId) AS count')
			->from('sproutlists_users')
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

		$users = $query->queryAll();

		$userModels = SproutLists_UserRecipientModel::populateModels($users, 'elementId');

		return $userModels;
	}

	/**
	 * Retrieve userIds by elementId & List
	 * @param  String $list       String representing subscription category.
	 * @param  Int $elementId     Int or Array of Ints for Elements.
	 * @return Array              Int or Array of Ints of User Ids.
	 */
	public function getSubscribers($criteria)
	{
		$listId = sproutLists()->getListId($criteria['list']);

		$query = craft()->db->createCommand()
			->select('userId')
			->from('sproutlists_users')
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

		$users = $query->queryAll();

		$userModels = SproutLists_UserRecipientModel::populateModels($users);

		return $userModels;
	}

	/**
	 * Retrieve subscription count based on list/userIds
	 * @param  String $list    		String representing subscription category.
	 * @param  Int/Array $userId 	Int or Array of Ints for User Ids.
	 * @return Array         		Subscription Count.
	 */
	public function getListCount($criteria)
	{
		$listId = sproutLists()->getListId($criteria['list']);

		$query = craft()->db->createCommand()
			->select('count(listId) as count')
			->from('sproutlists_users')
			->where(array('and', "listId = :listId"), array(':listId' => $listId) );

		if(isset($criteria['userId']))
		{
			$userId = $this->prepareIdsForQuery($criteria['userId']);

			$query->where(array('and', 'listId = :listId', array('in', 'userId', $userId)), array(':listId' => $listId));
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
	public function getSubscriberCount($criteria)
	{
		$listId = sproutLists()->getListId($criteria['list']);

		$query = craft()->db->createCommand()
			->select('count(listId) as count')
			->from('sproutlists_users')
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
}
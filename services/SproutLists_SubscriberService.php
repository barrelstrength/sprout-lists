<?php
namespace Craft;

class SproutLists_SubscriberService extends BaseApplicationComponent
{
	public function subscribe(SproutLists_SubscriberModel $model, $subscriptionModel)
	{
		if ($this->saveSubscriber($model))
		{
			$listRecord = $this->getListSubscriber($subscriptionModel);

			$listRecordIds = array();

			if ($listRecord == null)
			{
				$listRecordIds = $this->saveSubscriberListRelations($model);
			}

			if (!empty($model->subscriberLists))
			{
				$subscriberListIds = $model->subscriberLists;

				sproutLists()->saveListsElement($subscriberListIds, $subscriptionModel);
			}
		}
	}

	public function saveSubscriber(SproutLists_SubscriberModel $model)
	{
		$record = new SproutLists_SubscriberRecord();

		if (!empty($model->id))
		{
			$record = SproutLists_SubscriberRecord::model()->findById($model->id);
		}

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
		else
		{
			Craft::dd($record->getErrors());
		}

		return false;
	}

	public function saveSubscriberListRelations($model)
	{
		$subscriberId      = $model->id;
		$subscriberListIds = $model->subscriberLists;

		try
		{
			SproutLists_ListsSubscribersRecord::model()->deleteAll('subscriberId = :subscriberId', array(':subscriberId' => $subscriberId));
		}
		catch (Exception $e)
		{
			Craft::dd($e->getMessage());
		}

		$records = array();

		if (!empty($subscriberListIds))
		{
			foreach ($subscriberListIds as $listId)
			{
				$list = sproutLists()->getListById($listId);

				if ($list)
				{
					$relation = new SproutLists_ListsSubscribersRecord();

					$relation->subscriberId     = $subscriberId;
					$relation->listId = $list->id;

					$result = $relation->save(false);

					$records[] = $relation->id;

					if (!$result)
					{
						throw new Exception(print_r($relation->getErrors(), true));
					}
				}
				else
				{
					throw new Exception(
						Craft::t(
							'The Subscriber List with id {listId} does not exists.',
							array('listId' => $listId)
						)
					);
				}
			}
		}

		return $records;
	}

	public function getListSubscriber($subscriptionModel)
	{
		$listSubscriber = null;

		$listId = sproutLists()->getListId($subscriptionModel->list);

		$subscriberAttributes = array();

		if ($subscriptionModel->email != null)
		{
			$subscriberAttributes['email'] = $subscriptionModel->email;
		}

		if ($subscriptionModel->userId != null)
		{
			$subscriberAttributes['userId'] = $subscriptionModel->userId;
		}

		$subscriber = $this->getSubscriber($subscriberAttributes);

		if ($subscriber->id != null)
		{
			$subscriberAttributes = array(
				'listId'      => $listId,
				'subscriberId' => $subscriber->id
			);

			$listSubscriber = SproutLists_ListsSubscribersRecord::model()->findByAttributes($subscriberAttributes);
		}

		return $listSubscriber;
	}

	public function getListElement($subscriptionModel)
	{
		$handle = $subscriptionModel->list;

		$list = SproutLists_ListsRecord::model()->findByAttributes(array('handle' => $handle));

		$listSubscriber = null;

		if ($list != null)
		{
			$listElementAttributes = array(
					'listId'    => $list->id,
					'elementId' => $subscriptionModel->elementId
			);

			$listSubscriber = SproutLists_ListsElementsRelationsRecord::model()->findByAttributes($listElementAttributes);
		}

		return $listSubscriber;
	}

	public function unsubscribe($subscriptionModel)
	{
		$listElement = $this->getListElement($subscriptionModel);

		if ($listElement != null)
		{
			$listElement->delete();
		}
	}

	public function isSubscribed($subscriptionModel)
	{
		$listElement = $this->getListElement($subscriptionModel);

		return ($listElement != null) ? true : false;
	}

	public function getQuerySubscriptions($criteria)
	{
		$query = craft()->db->createCommand()
			->select('lists.*, listsubscribers.*, subscribers.*, listelements.*')
			->from('sproutlists_lists lists')
			->join('sproutlists_lists_subscribers listsubscribers', 'lists.id = listsubscribers.listId')
			->join('sproutlists_subscribers subscribers', 'subscribers.id = listsubscribers.subscriberId')
			->join('sproutlists_lists_subscribers_elements listelements', 'listelements.listId = lists.id');

		if (isset($criteria['list']))
		{
			$listId = sproutLists()->getListId($criteria['list']);

			$query->where(array('and', 'lists.id = :listId'), array(':listId' => $listId));
		}

		if (isset($criteria['userId']))
		{
			$query->where(array('and', 'subscribers.userId = :userId'), array(':userId' => $criteria['userId']));
		}

		if (isset($criteria['email']))
		{
			// Search by user ID or array of user IDs
			$emails = sproutLists()->prepareIdsForQuery($criteria['email']);

			$query->andWhere(array('and', array('in', 'subscribers.email', $emails)));
		}

		if (isset($criteria['elementId']))
		{
			$query->andWhere(array('and', 'listelements.elementId = :elementId'), array(':elementId' => $criteria['elementId']));
		}

		if (isset($criteria['elementIds']))
		{
			$query->andWhere(array('and', array('in', 'listelements.elementId', $criteria['elementIds'])));
		}

		if (isset($criteria['order']))
		{
			$query->order($criteria['order']);
		}

		if (isset($criteria['limit']))
		{
			$query->limit($criteria['limit']);
		}

		return $query;
	}

	public function getSubscriptions($criteria)
	{
		$results = $this->getQuerySubscriptions($criteria)->queryAll();

		return $results;
	}

	public function getSubscribers($criteria)
	{
		return $this->getSubscriptions($criteria);
	}

	public function getSubscriberCount($criteria)
	{
		$results = $this->getQuerySubscriptions($criteria)->queryAll();

		if (!empty($results))
		{
			return count($results);
		}

		return 0;
	}

	public function getListCount($criteria)
	{
		$count = 0;

		if (isset($criteria['id']))
		{
			$listId = $criteria['id'];

			$lists = SproutLists_ListsRecord::model()->with('subscribers')->findById($listId);
		}
		elseif ($criteria['list'])
		{
			$handle =$criteria['list'];

			$lists = SproutLists_ListsRecord::model()->with('subscribers')->findByAttributes(array('handle' => $handle));
		}

		if ($lists != null)
		{
			$count = count($lists->subscribers);
		}

		return $count;
	}

	public function getLists()
	{
		$records = SproutLists_SubscriberRecord::model()->with('subscriberLists')->findAll();
		$ids = array();
		$lists = array();

		if ($records)
		{
			foreach ($records as $record)
			{
				$ids[] = $record->id;
			}

			$query = craft()->db->createCommand()
				->select('listId')
				->where(array('in', 'subscriberId', $ids))
				->from('sproutlists_lists_subscribers')
				->group('listId');

			$results = $query->queryAll();

			if (!empty($results))
			{
				foreach ($results as $result)
				{
					$lists[] = sproutLists()->getListById($result['listId']);
				}
			}
		}

		return $lists;
	}

	public function getSubscriber(array $attributes)
	{
		$record = SproutLists_SubscriberRecord::model()->findByAttributes($attributes);

		$list = new SproutLists_SubscriberModel;

		if (!empty($record))
		{
			$list = SproutLists_SubscriberModel::populateModel($record);
		}

		return $list;
	}

	public function getSubscriberById($id)
	{
		$record = SproutLists_SubscriberRecord::model()->findById($id);

		$susbcriber = new SproutLists_SubscriberModel;

		if ($record != null)
		{
			$susbcriber = SproutLists_SubscriberModel::populateModel($record);

		}

		return $susbcriber;
	}

	public function deleteSubscriberById($id)
	{
		$model = sproutLists()->subscribers->getSubscriberById($id);

		if ($model->id != null)
		{
			if (craft()->elements->deleteElementById($model->id))
			{
				SproutLists_ListsSubscribersRecord::model()->deleteAll('subscriberId = :subscriberId', array(':subscriberId' => $model->id));
			}
		}

		return $model;
	}
}
<?php
namespace Craft;

class SproutLists_RecipientService extends BaseApplicationComponent
{
	public function subscribe(SproutLists_RecipientModel $model, $subscriptionModel)
	{
		if ($this->saveRecipient($model))
		{
			$listRecord = $this->getListRecipient($subscriptionModel);

			$listRecordIds = array();

			if ($listRecord == null)
			{
				$listRecordIds = $this->saveRecipientListRelations($model);
			}

			if (!empty($model->recipientLists))
			{
				$recipientListIds = $model->recipientLists;

				$this->saveListsElement($recipientListIds, $subscriptionModel);
			}
		}
	}

	public function saveRecipient(SproutLists_RecipientModel $model)
	{
		$record = new SproutLists_RecipientRecord;

		if (!empty($model->id))
		{
			$record = SproutLists_RecipientRecord::model()->findById($model->id);
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

	public function saveRecipientListRelations($model)
	{
		$recipientId      = $model->id;
		$recipientListIds = $model->recipientLists;

		try
		{
			SproutLists_ListsRecipientsRecord::model()->deleteAll('recipientId = :recipientId', array(':recipientId' => $recipientId));
		}
		catch (Exception $e)
		{
			Craft::dd($e->getMessage());
		}

		$records = array();

		if (!empty($recipientListIds))
		{
			foreach ($recipientListIds as $listId)
			{
				$list = sproutLists()->getListById($listId);

				if ($list)
				{
					$relation = new SproutLists_ListsRecipientsRecord();

					$relation->recipientId     = $recipientId;
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
							'The recipient list with id {listId} does not exists.',
							array('listId' => $listId)
						)
					);
				}
			}
		}

		return $records;
	}

	public function saveListsElement($listRecordIds, $subscriptionModel)
	{
		if (!empty($listRecordIds))
		{
			foreach ($listRecordIds as $listRecordId)
			{
				$record = new SproutLists_ListsElementsRelationsRecord;

				$record->elementId = $subscriptionModel->elementId;
				$record->type      = $subscriptionModel->type;
				$record->listId     = $listRecordId;

				$result = $record->save(false);
			}
		}
	}

	public function getListRecipient($subscriptionModel)
	{
		$listRecipient = null;

		$listId = sproutLists()->getListId($subscriptionModel->list);

		$recipientAttributes = array();

		if ($subscriptionModel->email != null)
		{
			$recipientAttributes['email'] = $subscriptionModel->email;
		}

		if ($subscriptionModel->userId != null)
		{
			$recipientAttributes['userId'] = $subscriptionModel->userId;
		}

		$recipient = $this->getRecipient($recipientAttributes);

		if ($recipient->id != null)
		{
			$recipientAttributes = array(
				'listId'      => $listId,
				'recipientId' => $recipient->id
			);

			$listRecipient = SproutLists_ListsRecipientsRecord::model()->findByAttributes($recipientAttributes);
		}

		return $listRecipient;
	}

	public function getListElement($subscriptionModel)
	{
		$listId = sproutLists()->getListId($subscriptionModel->list);

		if ($listId != null)
		{
			$listElementAttributes = array(
					'listId'    => $listId,
					'elementId' => $subscriptionModel->elementId
			);

			$listRecipient = SproutLists_ListsElementsRelationsRecord::model()->findByAttributes($listElementAttributes);
		}

		return $listRecipient;
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

		return ($listElement != null) ? true : false; // xxtempxx
	}

	public function getQuerySubscriptions($criteria)
	{
		$listId = sproutLists()->getListId($criteria['list']);

		$query = craft()->db->createCommand()
			->select('lists.*, listrecipients.*, recipients.*, listelements.*')
			->from('sproutlists_lists lists')
			->join('sproutlists_lists_recipients listrecipients', 'lists.id = listrecipients.listId')
			->join('sproutlists_recipients recipients', 'recipients.id = listrecipients.recipientId')
			->join('sproutlists_lists_recpients_elements listelements', 'listelements.listId = lists.id');

		if (isset($criteria['list']))
		{
			$query->where(array('and', 'lists.id = :listId'), array(':listId' => $listId));
		}

		if (isset($criteria['userId']))
		{
			$query->where(array('and', 'recipients.userId = :userId'), array(':userId' => $criteria['userId']));
		}

		if (isset($criteria['email']))
		{
			// Search by user ID or array of user IDs
			$emails = sproutLists()->prepareIdsForQuery($criteria['email']);

			$query->andWhere(array('and', array('in', 'recipients.email', $emails)));
		}

		if (isset($criteria['elementId']))
		{
			$query->andWhere(array('and', 'listelements.elementId = :elementId'), array(':elementId' => $criteria['elementId']));
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
		$records = SproutLists_RecipientRecord::model()->with('recipientLists')->findAll();

		$count = 0;
		if ($records)
		{
			$ids = array();

			foreach ($records as $record)
			{
				$ids[] = $record->id;
			}

			if (isset($criteria['id']))
			{
				$listId = $criteria['id'];
			}
			else
			{
				$listId = sproutLists()->getListId($criteria['list']);
			}

			$query = craft()->db->createCommand()
				->select('count(listId) as count')
				->where(array('in', 'recipientId', $ids))
				->andWhere(array('and', "listId = :listId"), array(':listId' => $listId) )
				->from('sproutlists_lists_recipients');

			$count = $query->queryScalar();
		}

		return $count;
	}

	public function getLists()
	{
		$records = SproutLists_RecipientRecord::model()->with('recipientLists')->findAll();
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
				->where(array('in', 'recipientId', $ids))
				->from('sproutlists_lists_recipients')
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

	public function getRecipient(array $attributes)
	{
		$record = SproutLists_RecipientRecord::model()->findByAttributes($attributes);

		$list = new SproutLists_RecipientModel;

		if (!empty($record))
		{
			$list = SproutLists_RecipientModel::populateModel($record);
		}

		return $list;
	}

	public function getRecipientById($id)
	{
		$record = SproutLists_RecipientRecord::model()->findById($id);

		$list = new SproutLists_RecipientModel;

		if (!empty($record))
		{
			$list = SproutLists_RecipientModel::populateModel($record);
		}

		return $list;
	}
}
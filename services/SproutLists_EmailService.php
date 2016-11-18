<?php
namespace Craft;

class SproutLists_EmailService extends BaseApplicationComponent
{
	public function subscribe(SproutLists_EmailRecipientModel $model, $subscriptionModel)
	{
		if ($this->saveRecipient($model))
		{
			$listRecords = $this->saveRecipientListRelations($model);

			if (!empty($listRecords))
			{
				$this->saveListsElement($listRecords, $subscriptionModel);
			}
		}
	}

	public function saveRecipient(SproutLists_EmailRecipientModel $model)
	{
		$record = new SproutLists_EmailRecipientRecord;

		if (!empty($model->id))
		{
			$record = SproutLists_EmailRecipientRecord::model()->findById($model->id);
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
				$record->listId = $listRecordId;

				$result = $record->save(false);
			}
		}
	}

	public function unsubscribe(SproutLists_EmailRecipientModel $model)
	{
		$listId = $model->listId;

		$result = craft()->db->createCommand()
			->delete('sproutlists_emails', array(
				'listId'    => $listId,
				'email'     => $model->email,
				'elementId' => $model->elementId,
			));

		if($result)
		{
			return true;
		}

		return false;
	}

	public function isSubscribed($criteria)
	{
		return false; // xxtempxx
		$query = craft()->db->createCommand()
			->select('email, elementId')
			->from('sproutlists_emails')
			->where(array(
				'AND',
				'listId = :listId',
				'email = :email',
				'elementId = :elementId',
			), array(
				':listId' => sproutLists()->getListId($criteria['list']),
				':email' => $criteria['email'],
				':elementId' => $criteria['elementId'],
			));

		$isSubscribed = $query->queryScalar();

		return ($isSubscribed) ? true : false;
	}

	public function getSubscriptions($criteria)
	{
		$listId = sproutLists()->getListId($criteria['list']);

		$query = craft()->db->createCommand()
			->select('email, elementId, dateCreated, COUNT(elementId) AS count')
			->from('sproutlists_emails')
			->group('elementId');

		if (isset($criteria['email']))
		{
			// Search by user ID or array of user IDs
			$emails = sproutLists()->prepareIdsForQuery($criteria['email']);

			$query->where(array('and', "listId = $listId", array('in', 'email', $emails)));
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

		$emails = $query->queryAll();

		$emailModels = SproutLists_EmailRecipientModel::populateModels($emails, 'elementId');

		return $emailModels;
	}

	public function getSubscribers($criteria)
	{
		$listId = sproutLists()->getListId($criteria['list']);

		$query = craft()->db->createCommand()
			->select('email')
			->from('sproutlists_emails')
			->where(array('listId = :listId'), array(':listId' => $listId));

		if (isset($criteria['elementId']))
		{
			$elementId = sproutLists()->prepareIdsForQuery($criteria['elementId']);
			$query->andWhere(array('in', 'elementId', $elementId));
		}
		else
		{
			$query->group('email');
		}

		if (isset($criteria['limit']))
		{
			$query->limit($criteria['limit']);
		}

		$emails = $query->queryAll();

		$emailModels = SproutLists_EmailRecipientModel::populateModels($emails);

		return $emailModels;
	}

	public function getListCount($criteria)
	{
		$records = SproutLists_EmailRecipientRecord::model()->with('recipientLists')->findAll();

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
			->from('sproutlists_emails')
			->where(array('listId = :listId'), array(':listId' => $listId));

		if(isset($criteria['elementId']))
		{
			$elementId = sproutLists()->prepareIdsForQuery($criteria['elementId']);

			$query->andWhere(array('in', 'elementId', $elementId));
		}
		else
		{
			$query->group('email');
		}

		$count = $query->queryScalar();

		return $count;
	}

	public function getLists()
	{
		$records = SproutLists_EmailRecipientRecord::model()->with('recipientLists')->findAll();
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

	public function getRecipientById($id)
	{
		$record = SproutLists_EmailRecipientRecord::model()->findById($id);

		$list = new SproutLists_EmailRecipientModel;

		if (!empty($record))
		{
			$list = SproutLists_EmailRecipientModel::populateModel($record);
		}

		return $list;
	}
}
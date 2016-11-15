<?php
namespace Craft;

class SproutLists_EmailService extends BaseApplicationComponent
{
	public function subscribe(SproutLists_EmailRecipientModel $model)
	{
		$record = new SproutLists_EmailRecipientRecord;

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
			->from('sproutlists_emails')
			->where(array('and', "listId = :listId"), array(':listId' => $listId) );

		if(isset($criteria['email']))
		{
			$email = sproutLists()->prepareIdsForQuery($criteria['email']);

			$query->where(array('and', 'listId = :listId', array('in', 'email', $email)), array(':listId' => $listId));
			$query->group('email');
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
		$query = craft()->db->createCommand()
			->select('listId')
			->from('sproutlists_emails')
			->group('listId');

		$results = $query->queryAll();

		$lists = array();

		if (!empty($results))
		{
			foreach ($results as $result)
			{
				$lists[] = SproutLists_ListsRecord::model()->findById($result['listId']);
			}
		}

		return $lists;
	}

	public function getRecipientById($id)
	{
		$record = SproutLists_EmailRecord::model()->findById($id);

		$list = new SproutLists_EmailModel;

		if (!empty($record))
		{
			$list = SproutLists_EmailModel::populateModel($record);
		}

		return $list;
	}

	public function saveRecipient(SproutLists_EmailModel $model)
	{
		$result = false;

		if ($model->id)
		{
			$record = SproutLists_EmailRecord::model()->findById($model->id);
		}
		else
		{
			$record = new SproutLists_EmailRecord();
		}

		$addressAttributes = $model->getAttributes();

		if (!empty($addressAttributes))
		{
			foreach ($addressAttributes as $handle => $value)
			{
				$record->setAttribute($handle, $value);
			}
		}

		$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;

		if ($record->validate())
		{
			if ($record->save(false))
			{
				$model->id = $record->id;

				if ($transaction && $transaction->active)
				{
					$transaction->commit();
				}

				$result = true;
			}
		}
		else
		{
			$model->addErrors($record->getErrors());
		}

		return $result;
	}
}
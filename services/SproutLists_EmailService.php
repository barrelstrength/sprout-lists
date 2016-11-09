<?php
namespace Craft;

class SproutLists_EmailService extends BaseApplicationComponent
{
	public function subscribe(SproutLists_EmailModel $model)
	{
		$record = new SproutLists_EmailRecord;

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

	public function unsubscribe(SproutLists_EmailModel $model)
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
			$emails = $this->prepareIdsForQuery($criteria['email']);

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

		$emailModels = SproutLists_EmailModel::populateModels($emails, 'elementId');

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
			$elementId = $this->prepareIdsForQuery($criteria['elementId']);
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

		$emailModels = SproutLists_EmailModel::populateModels($emails);

		return $emailModels;
	}

	public function listCount($criteria)
	{
		$listId = sproutLists()->getListId($criteria['list']);

		$query = craft()->db->createCommand()
			->select('count(listId) as count')
			->from('sproutlists_emails')
			->where(array('and', "listId = :listId"), array(':listId' => $listId) );

		if(isset($criteria['email']))
		{
			$email = $this->prepareIdsForQuery($criteria['email']);

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
			$elementId = $this->prepareIdsForQuery($criteria['elementId']);

			$query->andWhere(array('in', 'elementId', $elementId));
		}
		else
		{
			$query->group('email');
		}

		$count = $query->queryScalar();

		return $count;
	}

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
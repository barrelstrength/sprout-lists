<?php

namespace Craft;

class SproutLists_SubscribersService extends BaseApplicationComponent
{
	/**
	 * @param SproutLists_SubscriberModel $model
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function saveSubscriber(SproutLists_SubscriberModel $model)
	{
		$record = new SproutLists_SubscriberRecord();

		$result = false;

		if ($model->id)
		{
			$record = SproutLists_SubscriberRecord::model()->findById($model->id);
		}
		elseif ($model->email)
		{

			$user = craft()->users->getUserByUsernameOrEmail($model->email);

			if ($user != null)
			{
				$model->userId = $user->id;
			}
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

						$result = true;
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
			$model->addErrors($record->getErrors());
		}

		return $result;
	}

	public function getListSubscriber($subscriptionModel)
	{
		$listSubscriber = null;

		$list = sproutLists()->lists->getList(array(
			'list' => $subscriptionModel->list
		));

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
				'listId'       => $list->id,
				'subscriberId' => $subscriber->id
			);

			$listSubscriber = SproutLists_SubscriptionsRecord::model()->findByAttributes($subscriberAttributes);
		}

		return $listSubscriber;
	}

	public function getSubscribers($listIds)
	{
		$recipients = array();

		if (!empty($listIds))
		{
			foreach ($listIds as $listId)
			{
				$list = SproutLists_ListRecord::model()->findById($listId);

				if ($list != null)
				{
					$recipients = array_merge($recipients, $list->subscribers);
				}
			}
		}

		return $recipients;
	}

	public function updateTotalSubscribersCount($listId = null)
	{
		$result = false;

		if ($listId == null)
		{
			$lists = SproutLists_ListRecord::model()->with('subscribers')->findAll();

			if ($lists)
			{
				foreach ($lists as $list)
				{
					$result = $this->saveTotalSubscribersCount($list);
				}
			}
		}
		else
		{
			$list = SproutLists_ListRecord::model()->with('subscribers')->findById($listId);

			if ($list != null)
			{
				$result = $this->saveTotalSubscribersCount($list);
			}
		}

		return $result;
	}

	private function saveTotalSubscribersCount($list)
	{
		$count = count($list->subscribers);

		$list->total = $count;

		$result = $list->save();

		return $result;
	}

	public function getSubscriber(array $attributes)
	{
		$record = SproutLists_SubscriberRecord::model()->findByAttributes($attributes);

		$subscriberModel = new SproutLists_SubscriberModel();

		if (!empty($record))
		{
			$subscriberModel = SproutLists_SubscriberModel::populateModel($record);
		}

		// If no Subscriber was found, create one
		if (!$subscriberModel->id)
		{
			if (isset($attributes['userId']))
			{
				$subscriberModel->userId = $attributes['userId'];

				$user = craft()->users->getUserById($attributes['userId']);

				if ($user)
				{
					$subscriberModel->email = $user->email;
				}
			}

			if (isset($attributes['email']))
			{
				$subscriberModel->email = $attributes['email'];
			}

			$this->saveSubscriber($subscriberModel);
		}

		return $subscriberModel;
	}

	public function getSubscriberById($id)
	{
		$record = SproutLists_SubscriberRecord::model()->findById($id);

		$subscriber = new SproutLists_SubscriberModel();

		if ($record != null)
		{
			$subscriber = SproutLists_SubscriberModel::populateModel($record);
		}

		return $subscriber;
	}

	public function deleteSubscriberById($id)
	{
		$model = sproutLists()->subscribers->getSubscriberById($id);

		if ($model->id != null)
		{
			if (craft()->elements->deleteElementById($model->id))
			{
				SproutLists_SubscriptionsRecord::model()->deleteAll('subscriberId = :subscriberId', array(':subscriberId' => $model->id));
			}
		}

		sproutLists()->subscribers->updateTotalSubscribersCount();

		return $model;
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
	 * Sync SproutLists subscriber to craft_users if same email is found on save.
	 *
	 * @param Event $event
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function updateUserIdOnSave(Event $event)
	{
		$result = false;

		$userId = $event->params['user']->id;
		$email  = $event->params['user']->email;

		// First try to find a user by the Craft User ID
		$record = SproutLists_SubscriberRecord::model()->findByAttributes(array(
			'userId' => $userId
		));

		// If that doesn't work, try to find a user with a matching email address
		if ($record == null)
		{
			$record = SproutLists_SubscriberRecord::model()->findByAttributes(array(
				'userId' => null,
				'email'  => $email
			));

			// Assign the user ID to the subscriber with the matching email address
			$record->userId = $event->params['user']->id;
		}

		if ($record != null)
		{
			// If the user has updated their email, let's also update it for our Subscriber
			$record->email = $event->params['user']->email;

			$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;

			try
			{
				if ($record->save(false))
				{
					if ($transaction && $transaction->active)
					{
						$transaction->commit();
					}

					$result = true;
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

		return $result;
	}

	/**
	 * Remove any relationships between Sprout Lists Subscribers and Users who are deleted
	 *
	 * @param Event $event
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function updateUserIdOnDelete(Event $event)
	{
		$result = false;

		$userId = $event->params['user']->id;

		$record = SproutLists_SubscriberRecord::model()->findByAttributes(array(
			'userId' => $userId,
		));

		if ($record != null)
		{
			$record->userId = null;

			$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;

			try
			{
				if ($record->save(false))
				{
					if ($transaction && $transaction->active)
					{
						$transaction->commit();
					}

					$result = true;
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

		return $result;
	}
}
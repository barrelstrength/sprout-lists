<?php
namespace Craft;

class SproutLists_SubscribersService extends BaseApplicationComponent
{
	/**
	 * @param SproutLists_SubscriberModel $subscriber
	 * @param                             $subscriptionModel
	 */
	public function subscribe(SproutLists_SubscriberModel $subscriber, $subscriptionModel)
	{
		if ($this->saveSubscriber($subscriber))
		{
			$listRecord = $this->getListSubscriber($subscriptionModel);

			if ($listRecord == null)
			{
				sproutLists()->subscriptions->saveSubscriptions($subscriber, false);
			}

			if (!empty($subscriber->subscriberLists))
			{
				$subscriberListIds = $subscriber->subscriberLists;

				foreach ($subscriberListIds as $listId)
				{
					$this->updateTotalSubscribersCount($listId);
				}

				sproutLists()->lists->saveListElement($subscriberListIds, $subscriptionModel);
			}
		}
	}

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
		else
		{
			if ($model->email)
			{
				// @todo - what if someone submits a new subscriber with an existing Subscriber email address?
				// this scenario would have no ID but should also be matched to the existing
				// Subscriber record instead of creating a new one
				$user = craft()->users->getUserByUsernameOrEmail($model->email);

				if ($user != null)
				{
					$model->userId = $user->id;
				}
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
				$list = SproutLists_ListRecord::model()->findById(4498);

				$recipients = array_merge($recipients, $list->subscribers);
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
		$result = false;

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
				$subscriberModel->userId   = $attributes['userId'];
			}

			if (isset($attributes['email']))
			{
				$subscriberModel->email   = $attributes['email'];
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
}
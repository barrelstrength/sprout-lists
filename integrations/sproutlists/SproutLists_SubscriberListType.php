<?php

namespace Craft;

class SproutLists_SubscriberListType extends SproutListsBaseListType
{
	/**
	 * @return string
	 */
	public function getName()
	{
		return Craft::t('Subscriber');
	}

	/**
	 * The handle that refers to this list. Used as the 'type' when submitting forms.
	 *
	 * @return string
	 */
	public function getHandle()
	{
		return 'subscriber';
	}

	/**
	 * Saves a list.
	 *
	 * @param SproutLists_ListModel $list
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function saveList(SproutLists_ListModel $list)
	{
		if ($list->id)
		{
			$listRecord = SproutLists_ListRecord::model()->findById($list->id);
		}
		else
		{
			$listRecord = new SproutLists_ListRecord();
		}

		$modelAttributes = $list->getAttributes();

		if (!empty($modelAttributes))
		{
			foreach ($modelAttributes as $handle => $value)
			{
				$listRecord->setAttribute($handle, $value);
			}
		}

		$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;

		if ($listRecord->validate())
		{
			try
			{
				if (craft()->elements->saveElement($list))
				{
					$listRecord->id        = $list->id;
					$listRecord->elementId = $list->id;

					if ($list->totalSubscribers == null)
					{
						$listRecord->totalSubscribers = 0;
					}

					if ($listRecord->save(false))
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
			$list->addErrors($listRecord->getErrors());
		}

		return false;
	}

	/**
	 * Deletes a list.
	 *
	 * @param $listId
	 *
	 * @return bool
	 */
	public function deleteList($listId)
	{
		$listRecord = SproutLists_ListRecord::model()->findById($listId);

		if ($listRecord == null)
		{
			return false;
		}

		if ($listRecord->delete())
		{
			$subscriptions = SproutLists_SubscriptionRecord::model()->findByAttributes(array(
				'listId' => $listId
			));

			if ($subscriptions != null)
			{
				SproutLists_SubscriptionRecord::model()->deleteAll('listId = :listId', array(
					':listId' => $listId
				));
			}

			return true;
		}

		return false;
	}

	/**
	 * Gets lists.
	 *
	 * @return array
	 */
	public function getLists()
	{
		$lists = array();

		$listRecord = SproutLists_ListRecord::model()->findAll();

		if (!empty($listRecord))
		{
			$lists = SproutLists_ListModel::populateModels($listRecord);
		}

		return $lists;
	}

	/**
	 * Gets list with a given id.
	 *
	 * @param $listId
	 *
	 * @return BaseModel|SproutLists_ListModel
	 */
	public function getListById($listId)
	{
		$list = new SproutLists_ListModel();

		$listRecord = SproutLists_ListRecord::model()->findById($listId);

		if (!empty($listRecord))
		{
			$list = SproutLists_ListModel::populateModel($listRecord);
		}

		return $list;
	}

	/**
	 * Returns an array of all lists that have subscribers.
	 *
	 * @return array
	 */
	public function getListsWithSubscribers()
	{
		$records = SproutLists_SubscriberRecord::model()->with('subscriberLists')->findAll();
		$ids     = array();
		$lists   = array();

		if ($records)
		{
			foreach ($records as $record)
			{
				$ids[] = $record->id;
			}

			$query = craft()->db->createCommand()
				->select('listId')
				->where(array('in', 'subscriberId', $ids))
				->from('sproutlists_subscriptions')
				->group('listId');

			$results = $query->queryAll();

			if (!empty($results))
			{
				foreach ($results as $result)
				{
					$lists[] = $this->getListById($result['listId']);
				}
			}
		}

		return $lists;
	}

	/**
	 * @inheritDoc SproutListsBaseListType::subscribe()
	 *
	 * @param $criteria
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function subscribe($criteria)
	{
		$subscription = SproutLists_SubscriptionModel::populateModel($criteria);

		// Prepare our data
		$listCriteria = array(
			'handle' => $subscription->list
		);

		$subscriberCriteria = array(
			'userId' => $subscription->userId,
			'email'  => $subscription->email
		);

		// Remove any null values from our array, so we only query for what we have
		$subscriberCriteria = array_filter($subscriberCriteria, function ($var)
		{
			return !is_null($var);
		});

		// BEGIN TRANSACTION
		$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;

		try
		{
			if (!is_int($criteria['list']))
			{
				// If our List doesn't exist, create a List Element on the fly
				// @todo - review $listCriteria and $listHandle - seems a bit complicated. We create it as an array, rename it from where it goes into the method and comes out, and then treat the $listHandle variable as a string instead of an array?
				$list = $this->getOrCreateList($listCriteria, $subscription);
			}
			else
			{
				$list = SproutLists_ListRecord::model()->findById($criteria['list']);
			}

			// If it didn't work, rollback the transaction. Can't save a subscription without a List.
			if (!$list->id)
			{
				if ($transaction !== null)
				{
					$transaction->rollback();
				}

				return false;
			}

			// If our Subscriber doesn't exist, create a Subscriber Element on the fly
			$subscriber = $this->getSubscriber($subscriberCriteria);

			// If it didn't work, rollback the transaction. Can't save a subscription without a Subscriber.
			if (!$subscriber->id)
			{
				if ($transaction !== null)
				{
					$transaction->rollback();
				}

				return false;
			}

			$subscriptionRecord = new SproutLists_SubscriptionRecord();

			$subscriptionRecord->listId       = $list->id;
			$subscriptionRecord->subscriberId = $subscriber->id;

			// Create a criteria between our List Element and Subscriber Element
			if ($subscriptionRecord->save(false))
			{
				$this->updateTotalSubscribersCount($subscriptionRecord->listId);
			}

			// Commit the transaction regardless of whether we saved the entry, in case something changed
			// in onBeforeSaveEntry
			if ($transaction !== null)
			{
				$transaction->commit();
			}

			return true;
		}
		catch (\Exception $e)
		{
			if ($transaction !== null)
			{
				$transaction->rollback();
			}

			// Return false if not successful
			return false;
		}
		// END TRANSACTION
	}

	/**
	 * @inheritDoc SproutListsBaseListType::unsubscribe()
	 *
	 * @param $criteria
	 *
	 * @return bool
	 */
	public function unsubscribe($criteria)
	{
		// Determine the list from which the user will un-subscribe
		$listId = isset($criteria['listId']) ? $criteria['listId'] : null;

		if ($listId)
		{
			$list = SproutLists_ListRecord::model()->findById($listId);
		}
		else
		{
			// @todo - we may need to include listType here as handle could be the same for a list
			// on each List Type
			$list = SproutLists_ListRecord::model()->findByAttributes(array(
				'handle' => $criteria['list']
			));
		}

		if (!$list)
		{
			return false;
		}

		// Determine the subscriber that we will un-subscribe
		$subscriberRecord = new SproutLists_SubscriberRecord();

		if (isset($criteria['userId']))
		{
			$subscriberRecord = SproutLists_SubscriberRecord::model()->findByAttributes(array(
				'userId' => $criteria['userId']
			));
		}
		elseif (isset($criteria['email']))
		{
			$subscriberRecord = SproutLists_SubscriberRecord::model()->findByAttributes(array(
				'email' => $criteria['email']
			));
		}

		if (!isset($subscriberRecord->id))
		{
			return false;
		}

		// Delete the subscription that matches the List and Subscriber IDs
		$subscriptions = SproutLists_SubscriptionRecord::model()->deleteAllByAttributes(array(
			'listId'       => $list->id,
			'subscriberId' => $subscriberRecord->id
		));

		// Remove the user from the subscription
		if ($subscriptions != null)
		{
			$this->updateTotalSubscribersCount();

			return true;
		}

		return false;
	}

	/**
	 * @inheritDoc SproutListsBaseListType::isSubscribed()
	 *
	 * @param $criteria
	 *
	 * @return bool
	 */
	public function isSubscribed($criteria)
	{
		$results = $this->getSubscriptions($criteria);

		return (!empty($results)) ? true : false;
	}

	/**
	 * @inheritDoc SproutListsBaseListType::getSubscriptions()
	 *
	 * @param $criteria
	 *
	 * @return array|\CDbDataReader
	 */
	public function getSubscriptions($criteria)
	{
		$query = craft()->db->createCommand()
			->select('lists.*, subscribers.*, subscriptions.*')
			->from('sproutlists_lists lists')
			->join('sproutlists_subscriptions subscriptions', 'subscriptions.listId = lists.id')
			->join('sproutlists_subscribers subscribers', 'subscribers.id = subscriptions.subscriberId');

		if (isset($criteria['list']))
		{
			$list = SproutLists_ListRecord::model()->findByAttributes(array(
				'handle' => $criteria['list']
			));

			$listId = ($list != null) ? $list->id : 0;

			$query->andWhere(array('and', 'lists.id = :listId'), array(':listId' => $listId));
		}

		if (isset($criteria['userId']))
		{
			$query->andWhere(array('and', 'subscribers.userId = :userId'), array(':userId' => $criteria['userId']));
		}

		if (isset($criteria['email']))
		{
			$query->andWhere(array('and', array('in', 'subscribers.email', $criteria['email'])));
		}

		if (isset($criteria['elementId']))
		{
			$query->andWhere(array('and', array('in', 'lists.elementId', $criteria['elementId'])));
		}

		if (isset($criteria['order']))
		{
			$query->order($criteria['order']);
		}

		if (isset($criteria['limit']))
		{
			$query->limit($criteria['limit']);
		}

		return $query->queryAll();
	}

	/**
	 * @inheritDoc SproutListsBaseListType::getSubscribers()
	 *
	 * @param $criteria
	 *
	 * @return array
	 */
	public function getSubscribers($criteria)
	{
		$subscribers = array();

		if (empty($criteria))
		{
			return $subscribers;
		}

		$listHandle = $criteria['list'];

		$list = SproutLists_ListRecord::model()->findByAttributes(array(
			'handle' => $listHandle
		));

		$subscribers = $list->subscribers;

		return $subscribers;
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
		$model = $this->getSubscriberById($id);

		if ($model->id != null)
		{
			if (craft()->elements->deleteElementById($model->id))
			{
				SproutLists_SubscriptionRecord::model()->deleteAll('subscriberId = :subscriberId', array(':subscriberId' => $model->id));
			}
		}

		$this->updateTotalSubscribersCount();

		return $model;
	}

	/**
	 * @inheritDoc SproutListsBaseListType::getSubscriberCount()
	 *
	 * @param $criteria
	 *
	 * @return int
	 */
	public function getSubscriberCount($criteria)
	{
		$results = $this->getSubscriptions($criteria);

		if (!empty($results))
		{
			return count($results);
		}

		return 0;
	}

	/**
	 * @inheritDoc SproutListsBaseListType::getSubscriptionCount()
	 *
	 * @param $criteria
	 *
	 * @return int
	 */
	public function getSubscriptionCount($criteria)
	{
		return $this->getSubscriberCount($criteria);
	}

	/**
	 * Gets the HTML output for the lists sidebar on the Subscriber edit page.
	 *
	 * @return mixed
	 */
	public function getSubscriberListsHtml($subscriberId)
	{
		$default = array();

		$subscriber = $this->getSubscriberById($subscriberId);

		$listIds = $subscriber->getListIds();

		$lists   = $this->getLists();
		$options = array();

		if (count($lists))
		{
			foreach ($lists as $list)
			{
				$options[] = array(
					'label' => sprintf('%s', $list->name),
					'value' => $list->id
				);
			}
		}

		if (!empty($default))
		{
			$listIds = $default;
		}

		$html = craft()->templates->render('sproutlists/subscribers/checkboxlists', array(
			'options' => $options,
			'values'  => $listIds
		));

		return TemplateHelper::getRaw($html);
	}

	/**
	 * Gets or creates list.
	 *
	 * @todo - consider refactoring and cleaning up this method
	 *
	 * @param                                    $listHandle
	 * @param SproutLists_SubscriptionModel|null $subscription
	 *
	 * @return array|SproutLists_ListModel|mixed|null
	 */
	public function getOrCreateList($listHandle, SproutLists_SubscriptionModel $subscription = null)
	{
		$list = SproutLists_ListRecord::model()->findByAttributes(array(
			'handle' => $listHandle
		));

		// If no List exists, dynamically create one
		if ($list == null)
		{
			$list            = new SproutLists_ListModel();
			$list->name      = $subscription->list;
			$list->handle    = $subscription->list;
			$list->type      = 'subscriber';
			$list->elementId = $subscription->elementId;

			$this->saveList($list);
		}
		elseif ($subscription->elementId != null)
		{
			$model = SproutLists_ListModel::populateModel($list->getAttributes());

			$model->elementId = $subscription->elementId;

			$this->saveList($model);
		}

		return $list;
	}

	/**
	 * @param SproutLists_SubscriberModel $subscriber
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function saveSubscriber(SproutLists_SubscriberModel $subscriber)
	{
		$settings = craft()->plugins->getPlugin('sproutLists')->getSettings();

		$subscriberRecord = new SproutLists_SubscriberRecord();

		$result = false;

		if ($subscriber->id)
		{
			$subscriberRecord = SproutLists_SubscriberRecord::model()->findById($subscriber->id);
		}
		elseif ($subscriber->email)
		{
			// Sync updates with Craft User if User Sync enabled
			if ($settings->enableUserSync)
			{
				$user = craft()->users->getUserByUsernameOrEmail($subscriber->email);

				if ($user != null)
				{
					$subscriber->userId = $user->id;
				}
			}
		}

		$modelAttributes = $subscriber->getAttributes();

		if (!empty($modelAttributes))
		{
			foreach ($modelAttributes as $handle => $value)
			{
				$subscriberRecord->setAttribute($handle, $value);
			}
		}

		$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;

		if ($subscriberRecord->validate())
		{
			try
			{
				if (craft()->elements->saveElement($subscriber))
				{
					$subscriberRecord->id = $subscriber->id;

					if ($subscriberRecord->save(false))
					{
						// Save any related Subscriptions
						$this->saveSubscriptions($subscriber);

						// Sync updates with Craft User if User Sync enabled
						if ($subscriberRecord->userId != null && $settings->enableUserSync)
						{
							$user = craft()->users->getUserById($subscriberRecord->userId);

							$user->email = $subscriberRecord->email;

							craft()->users->saveUser($user);
						}

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
			$subscriber->addErrors($subscriberRecord->getErrors());
		}

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

	/**
	 * @todo - clarify that this takes a SproutLists_SubscriberModel with an array of listIds as an attribute
	 * @todo - what is $sync and why do we need it?
	 *
	 * @param SproutLists_SubscriberModel $subscriber
	 * @param bool                        $sync
	 *
	 * @return array
	 * @throws Exception
	 */
	public function saveSubscriptions(SproutLists_SubscriberModel $subscriber, $sync = true)
	{
		$subscriberId      = $subscriber->id;
		$subscriberListIds = $subscriber->subscriberLists;

		if ($sync === true)
		{
			try
			{
				SproutLists_SubscriptionRecord::model()->deleteAll('subscriberId = :subscriberId', array(
					':subscriberId' => $subscriberId
				));
			}
			catch (Exception $e)
			{
				SproutListsPlugin::log($e->getMessage(), LogLevel::Error);
			}
		}

		$records = array();

		if (!empty($subscriberListIds))
		{
			foreach ($subscriberListIds as $listId)
			{
				$list = $this->getListById($listId);

				if ($list)
				{
					$relation = new SproutLists_SubscriptionRecord();

					$relation->subscriberId = $subscriberId;
					$relation->listId       = $list->id;

					$result = $relation->save(false);

					$records[] = $relation->id;

					if (!$result)
					{
						throw new Exception(print_r($relation->getErrors(), true));
					}
				}
				else
				{
					throw new Exception(Craft::t('The Subscriber List with id {listId} does not exists.', array(
							'listId' => $listId)
					));
				}
			}
		}

		$this->updateTotalSubscribersCount();

		return $records;
	}

	/**
	 * Updates the totalSubscribers column in the db
	 *
	 * @param null $listId
	 *
	 * @return bool
	 */
	public function updateTotalSubscribersCount($listId = null)
	{
		if ($listId == null)
		{
			$lists = SproutLists_ListRecord::model()->with('subscribers')->findAll();
		}
		else
		{
			$list = SproutLists_ListRecord::model()->with('subscribers')->findById($listId);

			$lists = array($list);
		}

		if (count($lists))
		{
			foreach ($lists as $list)
			{
				$count = count($list->subscribers);

				$list->totalSubscribers = $count;

				$list->save();
			}

			return true;
		}

		return false;
	}
}
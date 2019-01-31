<?php

namespace Craft;

class SproutLists_SubscriberListType extends SproutListsBaseListType
{
	/**
	 * @return string
	 */
	public function getName()
	{
		return Craft::t('Subscriber Lists');
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

	// Lists
	// =========================================================================

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

		$listRecord->type   = $list->type;
		$listRecord->name   = $list->name;
		$listRecord->handle = $list->handle;

		$listRecord->validate();
		$list->addErrors($listRecord->getErrors());

		if (!$list->hasErrors())
		{
			$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;

			try
			{
				if (craft()->elements->saveElement($list))
				{
					$listRecord->id = $list->id;

					// Use an Element ID if we have it. Fallback to the List Element ID.
					$listRecord->elementId = is_numeric($list->elementId) ? $list->elementId : $list->id;

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
	 * @param null $subscriber
	 *
	 * @return array
	 */
	public function getLists($subscriber = null)
	{
		$lists = array();

		$subscriberRecord = null;

		if (!empty($subscriber->email) OR !empty($subscriber->userId))
		{
			$subscriberAttributes = array_filter(array(
				'email'  => $subscriber->email,
				'userId' => $subscriber->userId
			));

			$subscriberRecord = SproutLists_SubscriberRecord::model()->findByAttributes($subscriberAttributes);
		}

		if ($subscriberRecord == null)
		{
			// Only findAll if we are not looking for a specific Subscriber, otherwise we want to return null
			if (empty($subscriber->email))
			{
				$listRecord = SproutLists_ListRecord::model()->findAll();
			}
		}
		else
		{
			$listRecord = $subscriberRecord->subscriberLists;
		}

		if (!empty($listRecord))
		{
			$lists = SproutLists_ListModel::populateModels($listRecord);
		}

		return $lists;
	}

	/**
	 * @param null $subscriber
	 *
	 * @return int
	 */
	public function getListCount($subscriber = null)
	{
		$lists = $this->getLists($subscriber);

		return count($lists);
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
	 * Gets or creates list.
	 *
	 * @param SproutLists_SubscriptionModel $subscription
	 *
	 * @return BaseModel|SproutLists_ListModel
	 */
	public function getOrCreateList(SproutLists_SubscriptionModel $subscription)
	{
		$listRecord = SproutLists_ListRecord::model()->findByAttributes(array(
			'handle' => $subscription->listHandle
		));

		// If no List exists, dynamically create one
		if ($listRecord)
		{
			$list = SproutLists_ListModel::populateModel($listRecord);
		}
		else
		{
			$list            = new SproutLists_ListModel();
			$list->type      = 'subscriber';
			$list->elementId = $subscription->elementId;
			$list->name      = $subscription->listHandle;
			$list->handle    = $subscription->listHandle;

			$this->saveList($list);
		}

		return $list;
	}

	// Subscriptions
	// =========================================================================

	/**
	 * @inheritDoc SproutListsBaseListType::subscribe()
	 *
	 * @param $criteria
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function subscribe($subscription)
	{
		$settings = craft()->plugins->getPlugin('sproutLists')->getSettings();

		$subscriber = new SproutLists_SubscriberModel();

		if (!empty($subscription->email))
		{
			$subscriber->email = $subscription->email;
		}

		if (!empty($subscription->userId) && $settings->enableUserSync)
		{
			$subscriber->userId = $subscription->userId;
		}

		$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;

		try
		{
			// If our List doesn't exist, create a List Element on the fly
			$list = $this->getOrCreateList($subscription);

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
			$subscriber = $this->getSubscriber($subscriber);

			// If it didn't work, rollback the transaction. Can't save a subscription without a Subscriber.
			if (!$subscriber->id)
			{
				if ($transaction !== null)
				{
					$transaction->rollback();
				}

				return false;
			}

			$subscriptionRecord               = new SproutLists_SubscriptionRecord();
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
			if ($transaction && $transaction->active)
			{
				$transaction->rollback();
			}

			throw $e;
		}
	}

	/**
	 * @inheritDoc SproutListsBaseListType::unsubscribe()
	 *
	 * @param $subscription
	 *
	 * @return bool
	 */
	public function unsubscribe($subscription)
	{
		$settings = craft()->plugins->getPlugin('sproutLists')->getSettings();

		if ($subscription->id)
		{
			$list = SproutLists_ListRecord::model()->findById($subscription->id);
		}
		else
		{
			$list = SproutLists_ListRecord::model()->findByAttributes(array(
				'type'   => $subscription->listType,
				'handle' => $subscription->listHandle
			));
		}

		if (!$list)
		{
			return false;
		}

		// Determine the subscriber that we will un-subscribe
		$subscriberRecord = new SproutLists_SubscriberRecord();

		if (!empty($subscription->userId) && $settings->enableUserSync)
		{
			$subscriberRecord = SproutLists_SubscriberRecord::model()->findByAttributes(array(
				'userId' => $subscription->userId
			));
		}
		elseif (!empty($subscription->email))
		{
			$subscriberRecord = SproutLists_SubscriberRecord::model()->findByAttributes(array(
				'email' => $subscription->email
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
	public function isSubscribed($subscription)
	{
		$settings = craft()->plugins->getPlugin('sproutLists')->getSettings();

		if (empty($subscription->listHandle))
		{
			throw new Exception(Craft::t('Missing argument: `listHandle` is required by the isSubscribed variable'));
		}

		// We need a user ID or an email, however, if User Sync is not enabled, we need an email
		if ((empty($subscription->userId) && empty($subscription->email)) OR
			($settings->enableUserSync == false) && empty($subscription->email)
		)
		{
			throw new Exception(Craft::t('Missing argument: `userId` or `email` are required by the isSubscribed variable'));
		}

		$listId       = null;
		$subscriberId = null;

		$listRecord = SproutLists_ListRecord::model()->findByAttributes(array(
			'handle' => $subscription->listHandle
		));

		if ($listRecord)
		{
			$listId = $listRecord->id;
		}

		$attributes = array_filter(array(
			'email'  => $subscription->email,
			'userId' => $subscription->userId
		));

		$subscriberRecord = SproutLists_SubscriberRecord::model()->findByAttributes($attributes);

		if ($subscriberRecord)
		{
			$subscriberId = $subscriberRecord->id;
		}

		if ($listId != null && $subscriberId != null)
		{
			$subscriptionRecord = SproutLists_SubscriptionRecord::model()->findByAttributes(array(
				'subscriberId' => $subscriberId,
				'listId'       => $listId
			));

			if ($subscriptionRecord)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Saves a subscribers subscriptions.
	 *
	 * @param SproutLists_SubscriberModel $subscriber
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function saveSubscriptions(SproutLists_SubscriberModel $subscriber)
	{
		$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;

		try
		{
			SproutLists_SubscriptionRecord::model()->deleteAll('subscriberId = :subscriberId', array(
				':subscriberId' => $subscriber->id
			));

			if (!empty($subscriber->subscriberLists))
			{
				foreach ($subscriber->subscriberLists as $listId)
				{
					$list = $this->getListById($listId);

					if ($list)
					{
						$subscriptionRecord               = new SproutLists_SubscriptionRecord();
						$subscriptionRecord->subscriberId = $subscriber->id;
						$subscriptionRecord->listId       = $list->id;

						if (!$subscriptionRecord->save(false))
						{
							throw new Exception(print_r($subscriptionRecord->getErrors(), true));
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

			if ($transaction && $transaction->active)
			{
				$transaction->commit();
			}

			return true;
		}
		catch (\Exception $e)
		{
			SproutListsPlugin::log($e->getMessage(), LogLevel::Error);

			if ($transaction && $transaction->active)
			{
				$transaction->rollback();
			}

			throw $e;
		}
	}

	// Subscribers
	// =========================================================================

	/**
	 * @param SproutLists_SubscriberModel $subscriber
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function saveSubscriber(SproutLists_SubscriberModel $subscriber)
	{
		$settings = craft()->plugins->getPlugin('sproutLists')->getSettings();

		if ($subscriber->id)
		{
			$subscriberRecord = SproutLists_SubscriberRecord::model()->findById($subscriber->id);
		}
		else
		{
			$subscriberRecord = new SproutLists_SubscriberRecord();
		}

		$user = null;

		// Sync updates with Craft User if User Sync enabled
		if ($subscriber->email && $settings->enableUserSync)
		{
			$user = craft()->users->getUserByUsernameOrEmail($subscriber->email);

			if ($user != null)
			{
				$subscriber->userId = $user->id;
			}
		}

		$subscriberRecord->userId    = $subscriber->userId;
		$subscriberRecord->email     = $subscriber->email;
		$subscriberRecord->firstName = $subscriber->firstName;
		$subscriberRecord->lastName  = $subscriber->lastName;
		$subscriberRecord->firstName = $subscriber->firstName;

		$subscriberRecord->validate();
		$subscriber->addErrors($subscriberRecord->getErrors());

		if (!$subscriber->hasErrors())
		{
			$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;

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
							// If they changed their Subscriber info, update the Craft User info too
							$user->email     = $subscriberRecord->email;
							$user->firstName = $subscriberRecord->firstName;
							$user->lastName  = $subscriberRecord->lastName;

							craft()->users->saveUser($user);
						}

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
	 * @inheritDoc SproutListsBaseListType::getSubscribers()
	 *
	 * @param $list
	 *
	 * @return array|mixed
	 */
	public function getSubscribers($list)
	{
		if (empty($list->type))
		{
			throw new Exception(Craft::t("Missing argument: 'type' is required by the getSubscribers variable."));
		}

		if (empty($list->handle))
		{
			throw new Exception(Craft::t("Missing argument: 'listHandle' is required by the getSubscribers variable."));
		}

		$subscribers = array();

		if (empty($list))
		{
			return $subscribers;
		}

		$listRecord = SproutLists_ListRecord::model()->findByAttributes(array(
			'type'   => $list->type,
			'handle' => $list->handle
		));

		if ($listRecord != null)
		{
			$subscribers = SproutLists_SubscriberModel::populateModels($listRecord->subscribers);

			return $subscribers;
		}

		return $subscribers;
	}

	public function getSubscriberCount($list)
	{
		$subscribers = $this->getSubscribers($list);

		return count($subscribers);
	}

	/**
	 * Gets a subscriber.
	 *
	 * @param SproutLists_SubscriberModel $subscriber
	 *
	 * @return BaseModel|SproutLists_SubscriberModel
	 */
	public function getSubscriber(SproutLists_SubscriberModel $subscriber)
	{
		$attributes = array_filter(array(
			'email'  => $subscriber->email,
			'userId' => $subscriber->userId
		));

		$subscriberRecord = SproutLists_SubscriberRecord::model()->findByAttributes($attributes);

		if (!empty($subscriberRecord))
		{
			$subscriber = SproutLists_SubscriberModel::populateModel($subscriberRecord);
		}

		// If no Subscriber was found, create one
		if (!$subscriber->id)
		{
			if (isset($subscriber->userId))
			{
				$user = craft()->users->getUserById($subscriber->userId);

				if ($user)
				{
					$subscriber->email = $user->email;
				}
			}

			$this->saveSubscriber($subscriber);
		}

		return $subscriber;
	}

	/**
	 * Gets a subscriber with a given id.
	 *
	 * @param $id
	 *
	 * @return BaseModel|SproutLists_SubscriberModel
	 */
	public function getSubscriberById($id)
	{
		$subscriberRecord = SproutLists_SubscriberRecord::model()->findById($id);

		$subscriber = new SproutLists_SubscriberModel();

		if ($subscriberRecord != null)
		{
			$subscriber = SproutLists_SubscriberModel::populateModel($subscriberRecord);
		}

		return $subscriber;
	}

	/**
	 * Deletes a subscriber.
	 *
	 * @param $id
	 *
	 * @return BaseModel|SproutLists_SubscriberModel
	 */
	public function deleteSubscriberById($id)
	{
		$subscriber = $this->getSubscriberById($id);

		if ($subscriber->id != null)
		{
			if (craft()->elements->deleteElementById($subscriber->id))
			{
				SproutLists_SubscriptionRecord::model()->deleteAll('subscriberId = :subscriberId', array(':subscriberId' => $subscriber->id));
			}
		}

		$this->updateTotalSubscribersCount();

		return $subscriber;
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

		if (is_array($lists) && count($lists))
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

		$html = craft()->templates->render('sproutlists/subscribers/_subscriptionlists', array(
			'options' => $options,
			'values'  => $listIds
		));

		return TemplateHelper::getRaw($html);
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

		if (is_array($lists) && count($lists))
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
<?php

namespace Craft;

class SproutLists_SubscriberListType extends SproutListsBaseListType
{
	/**
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

		if ($subscription->type == null)
		{
			$subscription->type = 'subscriber';
		}

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
				$list = sproutLists()->lists->getListByHandle($listCriteria, $subscription);
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
			$subscriber = sproutLists()->subscribers->getSubscriber($subscriberCriteria);

			// If it didn't work, rollback the transaction. Can't save a subscription without a Subscriber.
			if (!$subscriber->id)
			{
				if ($transaction !== null)
				{
					$transaction->rollback();
				}

				return false;
			}

			$subscriptionRecord = new SproutLists_SubscriptionsRecord();

			$subscriptionRecord->listId       = $list->id;
			$subscriptionRecord->subscriberId = $subscriber->id;
			$subscriptionRecord->type         = 'subscriber';

			// Create a criteria between our List Element and Subscriber Element
			if ($subscriptionRecord->save(false))
			{
				sproutLists()->subscribers->updateTotalSubscribersCount($subscriptionRecord->listId);
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

	public function unsubscribe($criteria)
	{
		$result = false;

		if (!is_int($criteria['list']))
		{
			$list = SproutLists_ListRecord::model()->findByAttributes(array('handle' => $criteria['list']));
		}
		else
		{
			$list = SproutLists_ListRecord::model()->findById($criteria['list']);
		}

		if ($list)
		{
			$subscriber = new SproutLists_SubscriberRecord;

			if (isset($criteria['userId']))
			{
				$subscriber = SproutLists_SubscriberRecord::model()->findByAttributes(array('userId' => $criteria['userId']));
			}

			if (isset($criteria['email']))
			{
				$subscriber = SproutLists_SubscriberRecord::model()->findByAttributes(array('email' => $criteria['email']));
			}

			if (isset($subscriber->id))
			{
				$subscriptionCriteria = array(
					'listId'       => $list->id,
					'subscriberId' => $subscriber->id
				);

				$subscriptions = SproutLists_SubscriptionsRecord::model()->deleteAllByAttributes($subscriptionCriteria);

				// Remove the user from the subscription
				if ($subscriptions != null)
				{
					sproutLists()->subscribers->updateTotalSubscribersCount();

					$result = true;
				}
			}

			if (isset($criteria['elementId']))
			{
				$record = SproutLists_ListRecord::model()->findByAttributes(
					array('id' => $list->id, 'elementId' => $criteria['elementId']));

				// Update elementId to list id to remove relation or to un-subscribe
				if ($record != null)
				{
					$model = SproutLists_ListModel::populateModel($record->getAttributes());

					$model->elementId = $list->id;

					sproutLists()->lists->saveList($model);

					$result = true;
				}
			}
		}

		return $result;
	}

	public function isSubscribed($criteria)
	{
		$results = $this->getSubscriptions($criteria);

		return (!empty($results)) ? true : false;
	}

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

	public function getSubscribers($criteria)
	{
		return sproutLists()->subscribers->getSubscribers($criteria);
	}

	public function getSubscriberCount($criteria)
	{
		$results = $this->getSubscriptions($criteria);

		if (!empty($results))
		{
			return count($results);
		}

		return 0;
	}

	public function getSubscriptionCount($criteria)
	{
		return $this->getSubscriberCount($criteria);
	}
}
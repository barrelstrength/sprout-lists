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
	public function subscribe($subscription)
	{
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
			// If our List doesn't exist, create a List Element on the fly
			$list = sproutLists()->lists->getListByHandle($listCriteria, $subscription);

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
			// @todo - remove
			$subscriptionRecord->elementId = $list->id;

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

			throw $e;

			// Return false if not successful
			return false;
		}
		// END TRANSACTION
	}

	public function unsubscribe($subscription)
	{
		// Prepare our data

		$listCriteria = array(
			'handle' => $subscription->list
		);

		// Get the List ID (if we only have the Element ID)
		$list = sproutLists()->lists->getListByHandle($listCriteria);

		$subscriberCriteria = array(
			'userId' => $subscription->userId,
			'email'  => $subscription->email
		);

		// Remove any null values from our array, so we only query for what we have
		$subscriberCriteria = array_filter($subscriberCriteria, function ($var)
		{
			return !is_null($var);
		});

		// Get the Subscriber ID (if we only have the User ID)
		$subscriber = sproutLists()->subscribers->getSubscriber($subscriberCriteria);

		$subscriptionCriteria = array(
			'listId'       => $list->id,
			'subscriberId' => $subscriber->id,

			// @todo - remove
			'elementId'    => $list->id
		);

		$subscription = SproutLists_SubscriptionsRecord::model()->findByAttributes($subscriptionCriteria);

		// Remove the user from the subscription
		if ($subscription != null)
		{
			$subscription->delete();

			return true;
		}

		return false;
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
			// Search by user ID or array of user IDs
			$emails = $this->prepareIdsForQuery($criteria['email']);

			$query->andWhere(array('and', array('in', 'subscribers.email', $emails)));
		}

		if (isset($criteria['elementId']))
		{
			// Convert Element ID to array of potential List Element IDs
			$listElementIds = $this->getListElementIdsFromElementId($criteria['elementId']);

			$query->andWhere(array('and', array('in', 'subscriptions.listId', $listElementIds)));
		}

		if (isset($criteria['elementIds']))
		{
			$query->andWhere(array('and', array('in', 'subscriptions.elementId', $criteria['elementIds'])));
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

	/**
	 * Returns the ids of all List Elements that match a given Element ID
	 *
	 * @param $elementId
	 *
	 * @return array
	 */
	public function getListElementIdsFromElementId($elementId)
	{
		$results = craft()->db->createCommand()
			->select('id')
			->from('sproutlists_lists')
			->where(array('and', 'elementId = :elementId'), array(':elementId' => $elementId))
			->queryAll();

		return array_values(ArrayHelper::flattenArray($results));
	}
}
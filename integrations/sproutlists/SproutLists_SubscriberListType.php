<?php

namespace Craft;

class SproutLists_SubscriberListType extends SproutListsBaseListType
{
	public function subscribe($subscription)
	{
		$subscriptionModel = SproutLists_SubscriptionModel::populateModel($subscription);

		$listId = sproutLists()->getListId($subscriptionModel->list);

		$elementId = $subscriptionModel->elementId;
		$type      = $subscriptionModel->type;

		$attributes = array(
			'email' => $subscriptionModel->email,
			'subscriberLists' => array($listId)
		);

		if ($subscriptionModel->email != null)
		{
			$subscriberAttributes['email'] = $subscriptionModel->email;
		}

		if ($subscriptionModel->userId != null)
		{
			$subscriberAttributes['userId'] = $subscriptionModel->userId;

			$attributes['userId'] = $subscriptionModel->userId;
		}

		$model = sproutLists()->subscribers->getSubscriber($subscriberAttributes);

		if ($model->id == null)
		{
			$model = $model::populateModel($attributes);
		}
		else
		{
			$model->setAttribute('subscriberLists', $attributes['subscriberLists']);
		}

		return sproutLists()->subscribers->subscribe($model, $subscriptionModel);
	}

	public function unsubscribe($subscription)
	{
		$subscriptionModel = SproutLists_SubscriptionModel::populateModel($subscription);

		return sproutLists()->subscribers->unsubscribe($subscriptionModel);
	}

	public function isSubscribed($criteria)
	{
		return sproutLists()->subscribers->isSubscribed($criteria);
	}

	public function getSubscriptions($criteria)
	{
		return sproutLists()->subscribers->getSubscriptions($criteria);
	}

	public function getSubscribers($criteria)
	{
		return sproutLists()->subscribers->getSubscribers($criteria);
	}

	public function getListCount($criteria)
	{
		return sproutLists()->subscribers->getListCount($criteria);
	}

	public function getSubscriberCount($criteria)
	{
		return sproutLists()->subscribers->getSubscriberCount($criteria);
	}
}
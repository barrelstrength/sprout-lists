<?php

namespace Craft;

class SproutLists_RecipientListType extends SproutListsBaseListType
{
	public function subscribe($subscription)
	{
		$subscriptionModel = SproutLists_SubscriptionModel::populateModel($subscription);

		$listId = sproutLists()->getListId($subscriptionModel->list);

		$elementId = $subscriptionModel->elementId;
		$type      = $subscriptionModel->type;

		$attributes = array(
			'email' => $subscriptionModel->email,
			'recipientLists' => array($listId)
		);

		if ($subscriptionModel->email != null)
		{
			$recipientAttributes['email'] = $subscriptionModel->email;
		}

		if ($subscriptionModel->userId != null)
		{
			$recipientAttributes['userId'] = $subscriptionModel->userId;
		}

		$model = sproutLists()->listEmail->getRecipient($recipientAttributes);

		if ($model->id == null)
		{
			$model = SproutLists_RecipientRecipientModel::populateModel($attributes);
		}
		else
		{
			$model->setAttribute('recipientLists', $attributes['recipientLists']);
		}

		return sproutLists()->listEmail->subscribe($model, $subscriptionModel);
	}

	public function unsubscribe($subscription)
	{
		$subscriptionModel = SproutLists_SubscriptionModel::populateModel($subscription);

		return sproutLists()->listEmail->unsubscribe($subscriptionModel);
	}

	public function isSubscribed($criteria)
	{

		$subscriptionModel = SproutLists_SubscriptionModel::populateModel($criteria);
		return sproutLists()->listEmail->isSubscribed($subscriptionModel);
	}

	public function getSubscriptions($criteria)
	{
		return sproutLists()->listEmail->getSubscriptions($criteria);
	}

	public function getSubscribers($criteria)
	{
		return sproutLists()->listEmail->getSubscribers($criteria);
	}

	public function getListCount($criteria)
	{
		return sproutLists()->listEmail->getListCount($criteria);
	}

	public function getSubscriberCount($criteria)
	{
		return sproutLists()->listEmail->getSubscriberCount($criteria);
	}
}
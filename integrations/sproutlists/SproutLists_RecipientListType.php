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

			$attributes['userId'] = $subscriptionModel->userId;
		}

		$model = sproutLists()->listRecipient->getRecipient($recipientAttributes);

		if ($model->id == null)
		{
			$model = SproutLists_RecipientModel::populateModel($attributes);
		}
		else
		{
			$model->setAttribute('recipientLists', $attributes['recipientLists']);
		}

		return sproutLists()->listRecipient->subscribe($model, $subscriptionModel);
	}

	public function unsubscribe($subscription)
	{
		$subscriptionModel = SproutLists_SubscriptionModel::populateModel($subscription);

		return sproutLists()->listRecipient->unsubscribe($subscriptionModel);
	}

	public function isSubscribed($criteria)
	{

		$subscriptionModel = SproutLists_SubscriptionModel::populateModel($criteria);
		return sproutLists()->listRecipient->isSubscribed($subscriptionModel);
	}

	public function getSubscriptions($criteria)
	{
		return sproutLists()->listRecipient->getSubscriptions($criteria);
	}

	public function getSubscribers($criteria)
	{
		return sproutLists()->listRecipient->getSubscribers($criteria);
	}

	public function getListCount($criteria)
	{
		return sproutLists()->listRecipient->getListCount($criteria);
	}

	public function getSubscriberCount($criteria)
	{
		return sproutLists()->listRecipient->getSubscriberCount($criteria);
	}
}
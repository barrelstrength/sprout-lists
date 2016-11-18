<?php

namespace Craft;

class SproutLists_EmailListType extends SproutListsBaseListType
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

		$model = SproutLists_EmailRecipientModel::populateModel($attributes);

		return sproutLists()->listEmail->subscribe($model, $subscriptionModel);
	}

	public function unsubscribe($subscription)
	{
		$subscriptionModel = SproutLists_SubscriptionModel::populateModel($subscription);

		$subscription['listId'] = $listId;

		$model = SproutLists_EmailRecipientModel::populateModel($subscription);

		return sproutLists()->listEmail->unsubscribe($model);
	}

	public function isSubscribed($criteria)
	{
		return sproutLists()->listEmail->isSubscribed($criteria);
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
<?php

namespace Craft;

class SproutLists_EmailListType extends SproutListsBaseListType
{
	public function subscribe($subscription)
	{
		$listId = sproutLists()->getListId($subscription['list']);

		$subscription['listId'] = $listId;

		$model = SproutLists_EmailModel::populateModel($subscription);

		return sproutLists()->listEmail->subscribe($model);
	}

	public function unsubscribe($subscription)
	{
		$listId = sproutLists()->getListId($subscription['list']);

		$subscription['listId'] = $listId;

		$model = SproutLists_EmailModel::populateModel($subscription);

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
		return sproutLists()->listEmail->listCount($criteria);
	}

	public function getSubscriberCount($criteria)
	{
		return sproutLists()->listEmail->getSubscriberCount($criteria);
	}
}
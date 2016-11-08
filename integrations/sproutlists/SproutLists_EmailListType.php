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

	}

	public function getSubscribers($criteria)
	{

	}

	public function getListCount($criteria)
	{

	}

	public function subscriberCount($criteria)
	{

	}

	public function getListId($name)
	{

	}
}
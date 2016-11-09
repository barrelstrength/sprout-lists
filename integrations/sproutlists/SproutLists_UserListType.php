<?php

namespace Craft;

class SproutLists_UserListType extends SproutListsBaseListType
{
	public function subscribe($subscription)
	{
		$listId = sproutLists()->getListId($subscription['list']);

		$subscription['listId'] = $listId;

		$user = SproutLists_UserModel::populateModel($subscription);

		return sproutLists()->listUser->subscribe($user);
	}

	public function unsubscribe($subscription)
	{
		$listId = sproutLists()->getListId($subscription['list']);

		$subscription['listId'] = $listId;

		$user = SproutLists_UserModel::populateModel($subscription);

		return sproutLists()->listUser->unsubscribe($user);
	}

	public function isSubscribed($criteria)
	{
		return sproutLists()->listUser->isSubscribed($criteria);
	}

	public function getSubscriptions($criteria)
	{
		return sproutLists()->listUser->getSubscriptions($criteria);
	}

	public function getSubscribers($criteria)
	{
		return sproutLists()->listUser->getSubscribers($criteria);
	}

	public function getListCount($criteria)
	{
		return sproutLists()->listUser->getListCount($criteria);
	}

	public function getSubscriberCount($criteria)
	{
		return sproutLists()->listUser->getSubscriberCount($criteria);
	}
}
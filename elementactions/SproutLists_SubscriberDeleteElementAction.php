<?php

namespace Craft;

class SproutLists_SubscriberDeleteElementAction extends DeleteElementAction
{
	/**
	 * Deletes all selected subscribers
	 *
	 * @param ElementCriteriaModel $criteria
	 *
	 * @return bool
	 */
	public function performAction(ElementCriteriaModel $criteria)
	{
		parent::performAction($criteria);

		foreach ($criteria->ids() as $id)
		{
			SproutLists_SubscriptionRecord::model()->deleteAll('subscriberId = :subscriberId', array(
				':subscriberId' => $id
			));
		}

		// @todo - add support for other List Types
		$type     = 'subscriber';
		$listType = sproutLists()->lists->getListType($type);

		$listType->updateTotalSubscribersCount();

		return true;
	}
}

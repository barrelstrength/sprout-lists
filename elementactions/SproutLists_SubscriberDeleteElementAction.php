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

		$listType = sproutLists()->lists->getListType('subscriber');

		$listType->updateTotalSubscribersCount();

		return true;
	}
}

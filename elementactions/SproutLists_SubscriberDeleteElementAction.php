<?php
namespace Craft;

class SproutLists_SubscriberDeleteElementAction extends DeleteElementAction
{

	/**
	 * @param ElementCriteriaModel $criteria
	 *
	 * @return bool
	 */
	public function performAction(ElementCriteriaModel $criteria)
	{
		parent::performAction($criteria);

		// Delete all Subscriptions that relate to this subscriber
		// @todo - can we handle this with a foreign key instead?
		foreach ($criteria->ids() as $id)
		{
			SproutLists_SubscriptionsRecord::model()->deleteAll('subscriberId = :subscriberId', array(
				':subscriberId' => $id
			));
		}

		return true;
	}
}

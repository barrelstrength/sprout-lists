<?php
namespace Craft;

class SproutLists_ListDeleteElementAction extends DeleteElementAction
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
		foreach ($criteria->ids() as $id)
		{
			SproutLists_SubscriptionsRecord::model()->deleteAll('listId = :listId', array(
				':listId' => $id
			));
		}

		return true;
	}
}

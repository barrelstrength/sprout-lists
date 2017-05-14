<?php

namespace Craft;

class SproutLists_ListDeleteElementAction extends DeleteElementAction
{
	/**
	 * Deletes all selected lists
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
			SproutLists_SubscriptionsRecord::model()->deleteAll('listId = :listId', array(
				':listId' => $id
			));
		}

		return true;
	}
}

<?php
namespace Craft;

/**
 * Class SproutEmail_NotificationEmailDeleteElementAction
 *
 * @package Craft
 */
class SproutLists_RecipientDeleteElementAction extends DeleteElementAction
{

	/**
	 * @param ElementCriteriaModel $criteria
	 *
	 * @return bool
	 */
	public function performAction(ElementCriteriaModel $criteria)
	{
		parent::performAction($criteria);

		return true;
	}
}

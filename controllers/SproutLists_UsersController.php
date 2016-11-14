<?php
namespace Craft;

class SproutLists_UsersController extends BaseController
{
	public function actionEditUserRecipient(array $variables = array())
	{
		$userId = isset($variables['userId']) ? $variables['userId'] : null;

		$element = new SproutLists_UserRecipientModel;

		if ($userId)
		{
			$element = SproutLists_UserRecipientRecord::model()->findById($userId);
		}

		$this->renderTemplate('sproutlists/users/_edit', array(
			'userId'  => $userId,
			'element' => $element,
		  'recipientListsHtml' => ''
		));
	}
}
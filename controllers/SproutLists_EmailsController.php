<?php
namespace Craft;

class SproutLists_EmailsController extends BaseController
{
	public function actionEditEmailRecipient(array $variables = array())
	{
		$id = isset($variables['id']) ? $variables['id'] : null;
		$element = (isset($variables['element'])) ? $variables['element'] : null;

		if ($element == null)
		{
			$element = new SproutLists_EmailRecipientModel;

			if ($id)
			{
				$element = SproutLists_EmailRecipientModel::model()->findById($id);
			}
		}

		$this->renderTemplate('sproutlists/emails/_edit', array(
			'id'      => $id,
			'element' => $element,
		  'recipientListsHtml' => ''
		));
	}

	public function actionSaveRecipient()
	{
		$this->requirePostRequest();

		$list = craft()->request->getPost('recipient');

		$model = new SproutLists_EmailRecipientModel;

		if (!empty($list['id']))
		{
			$model = sproutLists()->listEmail->getRecipientById($list['id']);
		}

		$model->setAttributes($list);

		if ($model->validate())
		{
			$result = sproutLists()->listEmail->saveRecipient($model);

			if ($result !== false)
			{
				craft()->userSession->setNotice(Craft::t('Recipient saved.'));
			}

			$this->redirectToPostedUrl();
		}
		else
		{
			craft()->userSession->setError(Craft::t('Unable to recipeint.'));

			craft()->urlManager->setRouteVariables(array(
				'element' => $model
			));
		}
	}
}
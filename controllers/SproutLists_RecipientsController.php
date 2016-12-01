<?php
namespace Craft;

class SproutLists_RecipientsController extends BaseController
{
	public function actionEditRecipient(array $variables = array())
	{
		$id = isset($variables['id']) ? $variables['id'] : null;
		$element = (isset($variables['element'])) ? $variables['element'] : null;

		if ($element == null)
		{
			$element = new SproutLists_RecipientModel;

			if ($id)
			{
				$element = sproutLists()->listRecipient->getRecipientById($id);
			}
		}

		$this->renderTemplate('sproutlists/recipients/_edit', array(
			'id'      => $id,
			'element' => $element
		));
	}

	public function actionSaveRecipient()
	{
		$this->requirePostRequest();

		$recipient = craft()->request->getPost('sproutlists');

		$model = new SproutLists_RecipientModel;

		if (!empty($recipient['id']))
		{
			$model = sproutLists()->listRecipient->getRecipientById($recipient['id']);
		}

		$model->setAttributes($recipient);

		if ($model->validate())
		{
			if (sproutLists()->listRecipient->saveRecipient($model))
			{
				$result = sproutLists()->listRecipient->saveRecipientListRelations($model);

				if ($result !== false)
				{
					craft()->userSession->setNotice(Craft::t('Recipient saved.'));
				}
			}

			$this->redirectToPostedUrl($model);
		}
		else
		{
			craft()->userSession->setError(Craft::t('Unable to recipeint.'));

			craft()->urlManager->setRouteVariables(array(
				'element' => $model
			));
		}
	}

	public function actionDeleteRecipient()
	{
		$this->requirePostRequest();
		$id = craft()->request->getPost('recipient.id');

		if ($id != null)
		{
			$model = sproutLists()->listRecipient->getRecipientById($id);

			if (craft()->elements->deleteElementById($id))
			{
				SproutLists_ListsRecipientsRecord::model()->deleteAll('recipientId = :recipientId', array(':recipientId' => $id));

				$this->redirectToPostedUrl($model);
			}
		}
	}
}
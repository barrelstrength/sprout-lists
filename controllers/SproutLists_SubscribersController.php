<?php
namespace Craft;

class SproutLists_SubscribersController extends BaseController
{
	public function actionEditSubscriber(array $variables = array())
	{
		$id      = isset($variables['id']) ? $variables['id'] : null;
		$element = (isset($variables['element'])) ? $variables['element'] : null;

		if ($element == null)
		{
			$element = new SproutLists_SubscriberModel();

			if ($id)
			{
				$element = sproutLists()->subscribers->getSubscriberById($id);
			}
		}

		$this->renderTemplate('sproutlists/subscribers/_edit', array(
			'id'      => $id,
			'element' => $element
		));
	}

	public function actionSaveSubscriber()
	{
		$this->requirePostRequest();

		$subscriber = craft()->request->getPost('sproutlists');

		$model = new SproutLists_SubscriberModel();

		if (!empty($subscriber['id']))
		{
			$model = sproutLists()->subscribers->getSubscriberById($subscriber['id']);
		}

		$model->setAttributes($subscriber);

		if ($model->validate())
		{
			if (sproutLists()->subscribers->saveSubscriber($model))
			{
				$result = sproutLists()->subscribers->saveSubscriberListRelations($model);

				if ($result !== false)
				{
					craft()->userSession->setNotice(Craft::t('Subscriber saved.'));
				}
			}

			$this->redirectToPostedUrl($model);
		}
		else
		{
			craft()->userSession->setError(Craft::t('Unable to subscribe.'));

			craft()->urlManager->setRouteVariables(array(
				'element' => $model
			));
		}
	}

	public function actionDeleteSubscriber()
	{
		$this->requirePostRequest();

		$id = craft()->request->getPost('subscriber.id');

		if ($id != null)
		{
			$model = sproutLists()->subscribers->getSubscriberById($id);

			if (craft()->elements->deleteElementById($id))
			{
				SproutLists_ListsSubscribersRecord::model()->deleteAll('subscriberId = :subscriberId', array(':subscriberId' => $id));

				$this->redirectToPostedUrl($model);
			}
		}
	}
}
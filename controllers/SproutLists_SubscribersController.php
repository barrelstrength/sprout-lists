<?php
namespace Craft;

class SproutLists_SubscribersController extends BaseController
{
	/**
	 * Prepare variables for Subscriber Edit Template
	 *
	 * @param array $variables
	 */
	public function actionEditSubscriberTemplate(array $variables = array())
	{
		$id      = isset($variables['id']) ? $variables['id'] : null;
		$element = (isset($variables['element'])) ? $variables['element'] : null;

		if ($element == null)
		{
			$element = new SproutLists_SubscriberModel();

			// @todo - why is this nested in another if statement?
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

	/**
	 * Saves a Subscriber
	 */
	public function actionSaveSubscriber()
	{
		$this->requirePostRequest();

		$post = craft()->request->getRequiredPost('sproutlists');

		$model = new SproutLists_SubscriberModel();

		if (isset($post['id']) && $post['id'])
		{
			$model = sproutLists()->subscribers->getSubscriberById($post['id']);
		}

		$model->setAttributes($post);

		if (sproutLists()->subscribers->saveSubscriber($model))
		{
			$result = sproutLists()->subscriptions->saveSubscriptions($model);

			sproutLists()->subscribers->updateTotalSubscribersCount();

			if ($result !== false)
			{
				craft()->userSession->setNotice(Craft::t('Subscriber saved.'));
			}

			$this->redirectToPostedUrl($model);
		}
		else
		{
			craft()->userSession->setError(Craft::t('Unable to save subscriber.'));

			craft()->urlManager->setRouteVariables(array(
				'element' => $model
			));
		}
	}

	/**
	 * Deletes a Subscriber
	 */
	public function actionDeleteSubscriber()
	{
		$this->requirePostRequest();

		$id = craft()->request->getRequiredPost('sproutlists.id');

		if ($model = sproutLists()->subscribers->deleteSubscriberById($id))
		{
			$this->redirectToPostedUrl($model);
		}
		else
		{
			craft()->userSession->setError(Craft::t('Unable to delete subscriber.'));

			craft()->urlManager->setRouteVariables(array(
				'element' => $model
			));
		}
	}
}
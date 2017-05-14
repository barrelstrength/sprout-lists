<?php

namespace Craft;

class SproutLists_SubscribersController extends BaseController
{
	/**
	 * Prepare variables for Subscriber Edit Template
	 *
	 * @param array $variables
	 *
	 * @return null
	 */
	public function actionEditSubscriberTemplate(array $variables = array())
	{
		// @todo - add support for other List Types
		$type        = 'subscriber';
		$listType    = sproutLists()->lists->getListType($type);
		$listTypes[] = $listType;

		$subscriber = new SproutLists_SubscriberModel();

		if (isset($variables['subscriber']))
		{
			$subscriber = $variables['subscriber'];
		}
		elseif (isset($variables['id']))
		{
			$subscriber = $listType->getSubscriberById($variables['id']);
		}

		$this->renderTemplate('sproutlists/subscribers/_edit', array(
			'subscriber' => $subscriber,
			'listTypes'  => $listTypes
		));
	}

	/**
	 * Saves a subscriber
	 *
	 * @return null
	 */
	public function actionSaveSubscriber()
	{
		$this->requirePostRequest();

		$post         = craft()->request->getRequiredPost('sproutlists');
		$subscriberId = isset($post['id']) ? $post['id'] : null;

		// @todo - add support for other List Types
		$type        = 'subscriber';
		$listType    = sproutLists()->lists->getListType($type);

		$subscriber = new SproutLists_SubscriberModel();

		if ($subscriberId)
		{
			$subscriber = $listType->getSubscriberById($subscriberId);
		}

		$subscriber->setAttributes($post);

		if ($listType->saveSubscriber($subscriber))
		{
			craft()->userSession->setNotice(Craft::t('Subscriber saved.'));

			$this->redirectToPostedUrl($subscriber);
		}
		else
		{
			craft()->userSession->setError(Craft::t('Unable to save subscriber.'));

			craft()->urlManager->setRouteVariables(array(
				'subscriber' => $subscriber
			));
		}
	}

	/**
	 * Deletes a subscriber
	 *
	 * @return null
	 */
	public function actionDeleteSubscriber()
	{
		$this->requirePostRequest();

		$subscriberId = craft()->request->getRequiredPost('sproutlists.id');

		// @todo - add support for other List Types
		$type        = 'subscriber';
		$listType    = sproutLists()->lists->getListType($type);

		if ($subscriber = $listType->deleteSubscriberById($subscriberId))
		{
			craft()->userSession->setNotice(Craft::t('Subscriber deleted.'));

			$this->redirectToPostedUrl($subscriber);
		}
		else
		{
			craft()->userSession->setError(Craft::t('Unable to delete subscriber.'));

			craft()->urlManager->setRouteVariables(array(
				'subscriber' => $subscriber
			));
		}
	}
}
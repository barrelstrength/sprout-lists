<?php
namespace Craft;

/**
 * Lists controller
 *
 */
class SproutLists_ListsController extends BaseController
{
	/**
	 * Action to submit new subscription
	 * @return boolean/array bool if successful/redirect
	 *                       array of errors
	 */
	public function actionSubscribe()
	{
		if (craft()->request->getPost('userId') != null)
		{
			$subscription['userId'] = craft()->request->getPost('userId');
		}

		if (craft()->request->getPost('email') != null)
		{
			$subscription['email'] = craft()->request->getPost('email');
		}

		$subscription['elementId'] = craft()->request->getRequiredPost('elementId');
		$subscription['list'] = craft()->request->getRequiredPost('list');

		$type = 'user';

		if (craft()->request->getPost('type') != null)
		{
			$type = craft()->request->getPost('type');
		}

		$listType = sproutLists()->getListType($type);

		if (!$listType->subscribe($subscription))
		{
			// Save element type for emails
			$listType->afterSubscribe($subscription);

			if (craft()->request->isAjaxRequest())
			{
				$this->returnJson(array(
					'success' => true,
				));
			} 
			else 
			{
				$this->redirectToPostedUrl();
			}
		} 
		else 
		{
			// @todo - might need to add settings to determine what errors matter
			// Subscriptions may require uniqueness, voting may allow multiple subscribes
			$errors = array(Craft::t('Subscription did not save.'));

			if (craft()->request->isAjaxRequest())
			{
				$this->returnJson(array(
					'errors' => $errors,
				));
			} 
			else 
			{
				craft()->urlManager->setRouteVariables(array(
					'errors' => $errors
				));

				$this->redirectToPostedUrl();
			}
		}
	}

	/**
	 * Action to unsubscribe to an element.
	 * @return boolean/array bool if successful/redirect
	 *                       array of errors
	 */
	public function actionUnsubscribe()
	{
		if (craft()->request->getPost('userId') != null)
		{
			$subscription['userId'] = craft()->request->getPost('userId');
		}

		if (craft()->request->getPost('email') != null)
		{
			$subscription['email'] = craft()->request->getPost('email');
		}

		$subscription['elementId'] = craft()->request->getRequiredPost('elementId');
		$subscription['list'] = craft()->request->getRequiredPost('list');

		$type = 'user';

		if (craft()->request->getPost('type') != null)
		{
			$type = craft()->request->getPost('type');
		}

		$listType = sproutLists()->getListType($type);

		if (!$listType->unsubscribe($subscription))
		{
			if (craft()->request->isAjaxRequest())
			{
				$this->returnJson(array(
					'success' => true,
				));
			} 
			else 
			{
				$this->redirectToPostedUrl();
			}
		} 
		else 
		{
			if (craft()->request->isAjaxRequest())
			{
				$this->returnJson(array(
					'response' => 'fail',
				));
			} 
			else 
			{
				craft()->urlManager->setRouteVariables(array(
					'response' => 'fail',
				));

				$this->redirectToPostedUrl();
			}
		}

		$this->redirectToPostedUrl();
	}
}
<?php
namespace Craft;

/**
 * Lists controller
 *
 */
class SproutSubscribe_ListsController extends BaseController
{
	/**
	 * Action to submit new subscription
	 * @return boolean/array bool if successful/redirect
	 *                       array of errors
	 */
	public function actionSubscribe()
	{ 
		$userId = craft()->request->getRequiredPost('userId');
		$elementId = craft()->request->getPost('elementId');
		$keyName = craft()->request->getPost('key');

		if (!$userId OR !$elementId)
		{
			return false;
		}

		// Create a new Subscription
		$status = craft()->sproutSubscribe_subscription->newSubscription($keyName);

		if ($status)
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
			// @TODO Should be a Model
			$errors = $record->getErrors();

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
		$userId = craft()->request->getRequiredPost('userId');
		$elementId = craft()->request->getPost('elementId');
		$keyName = craft()->request->getPost('key');

		if (!$userId OR !$elementId)
		{
			return false;
		}

		$status = craft()->sproutSubscribe_subscription->unsubscribe($keyName);

		if ($status)
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
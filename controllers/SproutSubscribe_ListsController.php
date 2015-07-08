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
		$userId = craft()->userSession->id;
		$elementId = craft()->request->getPost('elementId');
		$keyName = craft()->request->getPost('key');

		if (!$userId OR !$elementId)
		{
			return false;
		}

		$keyId = craft()->sproutSubscribe_subscription->getKeyId($keyName);

		$record = new SproutSubscribe_SubscriptionRecord;
		$record->userId = $userId;
		$record->elementId = $elementId;
		$record->listsId = $keyId;

		if ($record->save())
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
		$userId = craft()->userSession->id;
		$elementId = craft()->request->getPost('elementId');
		$keyName = craft()->request->getPost('key');

		if (!$userId OR !$elementId)
		{
			return false;
		}

		$keyId = craft()->sproutSubscribe_subscription->getKeyId($keyName);

		// @TODO refactor for validation of result && json responses
		$result = craft()->db->createCommand()
			->delete('sproutsubscribe_subscriptions', array(
				'userId' => $userId,
				'elementId' => $elementId,
				'listsId' => $keyId
			));

		if ($result)
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
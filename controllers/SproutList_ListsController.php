<?php
namespace Craft;

/**
 * Lists controller
 *
 */
class SproutList_ListsController extends BaseController
{
	/**
	 * Action to submit new subscription
	 * @return boolean/array bool if successful/redirect
	 *                       array of errors
	 */
	public function actionSubscribe()
	{
		$subscription['userId'] = craft()->request->getRequiredPost('userId');
		$subscription['elementId'] = craft()->request->getRequiredPost('elementId');
		$subscription['list'] = craft()->request->getRequiredPost('list');

		$subscriptionModel = SproutList_SubscriptionModel::populateModel($subscription);

		if (!craft()->sproutList_subscription->subscribe($subscriptionModel))
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
		$subscription['userId'] = craft()->request->getRequiredPost('userId');
		$subscription['elementId'] = craft()->request->getRequiredPost('elementId');
		$subscription['list'] = craft()->request->getRequiredPost('list');

		$subscriptionModel = SproutList_SubscriptionModel::populateModel($subscription);

		if (!craft()->sproutList_subscription->unsubscribe($subscriptionModel))
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
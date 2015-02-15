<?php
namespace Craft;

class SproutSubscribe_ListsController extends BaseController
{
	public function actionSubscribe()
	{ 
		$userId = craft()->userSession->id;
		$elementId = craft()->request->getPost('elementId');

		if (!$userId OR !$elementId)
		{ 
			return false;
		} 

		if (craft()->sproutSubscribe->subscribe($userId, $elementId))
		{
			if (craft()->request->isAjaxRequest())
			{
				$this->returnJson(array(
				  'success' => 'success',
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

	public function actionUnsubscribe()
	{
		$userId = craft()->userSession->id;
		$elementId = craft()->request->getPost('elementId');

		if (!$userId OR !$elementId)
		{
			return false;
		}

		if (craft()->sproutSubscribe->unsubscribe($userId, $elementId))
		{       
			if (craft()->request->isAjaxRequest())
			{
				$this->returnJson(array(
				  'success' => 'success',
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
	}
}
<?php
namespace Craft;

class SproutLists_ListsController extends BaseController
{
	/**
	 * Prepare variables for the List Edit Template
	 *
	 * @param array $variables
	 */
	public function actionEditListTemplate(array $variables = array())
	{
		$list   = new SproutLists_ListModel;
		$listId = null;
		$continueEditingUrl = null;

		if (isset($variables['list']))
		{
			$list = $variables['list'];
		}
		elseif (isset($variables['listId']))
		{
			$listId = $variables['listId'];

			$list = sproutLists()->lists->getListById($listId);

			$continueEditingUrl = 'sproutlists/lists/edit/' . $listId;
		}

		$this->renderTemplate('sproutlists/lists/_edit', array(
			'listId'             => $listId,
			'list'               => $list,
			'continueEditingUrl' => $continueEditingUrl
		));
	}

	/**
	 * Saves a List
	 */
	public function actionSaveList()
	{
		$this->requirePostRequest();

		$list = craft()->request->getPost('sproutlists');

		$model = new SproutLists_ListModel();

		if (!empty($list['id']))
		{
			$model = sproutLists()->lists->getListById($list['id']);
		}

		$model->setAttributes($list);

		if (sproutLists()->lists->saveList($model))
		{
			craft()->userSession->setNotice(Craft::t('List saved.'));

			$this->redirectToPostedUrl();
		}
		else
		{
			craft()->userSession->setError(Craft::t('Unable to save list.'));

			craft()->urlManager->setRouteVariables(array(
				'list' => $model
			));
		}
	}

	/**
	 * Deletes a List
	 */
	public function actionDeleteList()
	{
		$this->requirePostRequest();

		$listId = craft()->request->getRequiredPost('sproutlists.id');

		if (sproutLists()->lists->deleteList($listId))
		{
			if (craft()->request->isAjaxRequest())
			{
				$this->returnJson(array(
					'success' => true
				));
			}
			else
			{
				craft()->userSession->setNotice(Craft::t('List deleted.'));

				$this->redirectToPostedUrl();
			}
		}
		else
		{
			if (craft()->request->isAjaxRequest())
			{
				$this->returnJson(array(
					'success' => false
				));
			}
			else
			{
				craft()->userSession->setError(Craft::t("Couldn't delete List."));

				$this->redirectToPostedUrl();
			}
		}
	}

	/**
	 *  Adds a Subscriber to a List
	 *
	 * @return boolean true/false if successful
	 * @return array   array of errors if fail
	 */
	public function actionSubscribe()
	{
		$criteria['list']      = craft()->request->getRequiredPost('list');
		$criteria['userId']    = craft()->request->getPost('userId');
		$criteria['email']     = craft()->request->getPost('email');

		$type = craft()->request->getPost('type');

		$listType = sproutLists()->lists->getListType($type);

		// Remove any null values from our array, so we only query for what we have
		$criteria = array_filter($criteria, function ($var)
		{
			return !is_null($var);
		});

		$subscriptionModel = SproutLists_SubscriptionModel::populateModel($criteria);

		if ($listType->subscribe($subscriptionModel))
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
	 * Remove a Subscriber from a List
	 *
	 * @return boolean true/false if successful
	 * @return array   array of errors if fail
	 */
	public function actionUnsubscribe()
	{
		$criteria['list']      = craft()->request->getRequiredPost('list');
		$criteria['userId']    = craft()->request->getPost('userId');
		$criteria['email']     = craft()->request->getPost('email');

		$type = craft()->request->getPost('type');

		$listType = sproutLists()->lists->getListType($type);

		// Remove any null values from our array, so we only query for what we have
		$criteria = array_filter($criteria, function ($var)
		{
			return !is_null($var);
		});

		if ($listType->unsubscribe($criteria))
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
					'success' => false
				));
			}
			else
			{
				craft()->urlManager->setRouteVariables(array(
					'success' => false
				));

				$this->redirectToPostedUrl();
			}
		}
	}
}
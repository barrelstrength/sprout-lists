<?php

namespace Craft;

class SproutLists_ListsController extends BaseController
{
	/**
	 * Prepare variables for the List Edit Template
	 *
	 * @param array $variables
	 *
	 * @return null
	 */
	public function actionEditListTemplate(array $variables = array())
	{
		$list               = new SproutLists_ListModel;
		$listId             = null;
		$continueEditingUrl = null;

		// @todo - add support for other List Types
		$type     = 'subscriber';
		$listType = sproutLists()->lists->getListType($type);

		if (isset($variables['list']))
		{
			$list = $variables['list'];
		}
		elseif (isset($variables['listId']))
		{
			$listId = $variables['listId'];

			$list = $listType->getListById($listId);

			$continueEditingUrl = 'sproutlists/lists/edit/' . $listId;
		}

		$this->renderTemplate('sproutlists/lists/_edit', array(
			'listId'             => $listId,
			'list'               => $list,
			'continueEditingUrl' => $continueEditingUrl
		));
	}

	/**
	 * Saves a list
	 *
	 * @return null
	 */
	public function actionSaveList()
	{
		$this->requirePostRequest();

		$post   = craft()->request->getPost('sproutlists');
		$listId = isset($post['id']) ? $post['id'] : null;

		// @todo - add support for other List Types
		$type     = 'subscriber';
		$listType = sproutLists()->lists->getListType($type);

		$list = new SproutLists_ListModel();

		if ($listId)
		{
			$list = $listType->getListById($listId);
		}

		$list->setAttributes($post);

		if ($listType->saveList($list))
		{
			craft()->userSession->setNotice(Craft::t('List saved.'));

			$this->redirectToPostedUrl();
		}
		else
		{
			craft()->userSession->setError(Craft::t('Unable to save list.'));

			craft()->urlManager->setRouteVariables(array(
				'list' => $list
			));
		}
	}

	/**
	 * Deletes a list.
	 *
	 * @return null
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
				craft()->userSession->setError(Craft::t('Unable to delete list.'));

				$this->redirectToPostedUrl();
			}
		}
	}

	/**
	 *  Adds a subscriber to a list
	 *
	 * @return boolean true/false if successful
	 * @return array   array of errors if fail
	 */
	public function actionSubscribe()
	{
		$criteria['list']   = craft()->request->getRequiredPost('list');
		$criteria['userId'] = craft()->request->getPost('userId');
		$criteria['email']  = craft()->request->getPost('email');

		if (craft()->request->getPost('elementId') != null)
		{
			$criteria['elementId'] = craft()->request->getPost('elementId');
		}

		$type = craft()->request->getPost('type', 'subscriber');

		$listType = sproutLists()->lists->getListType($type);

		// Remove any null values from our array, so we only query for what we have
		$criteria = array_filter($criteria, function ($var)
		{
			return !is_null($var);
		});

		if ($listType->subscribe($criteria))
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
			$errors = array(Craft::t('Unable to save subscription.'));

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
	 * Removes a subscriber from a list
	 *
	 * @return boolean true/false if successful
	 * @return array   array of errors if fail
	 */
	public function actionUnsubscribe()
	{
		$criteria['list']   = craft()->request->getRequiredPost('list');
		$criteria['userId'] = craft()->request->getPost('userId');
		$criteria['email']  = craft()->request->getPost('email');

		if (craft()->request->getPost('elementId') != null)
		{
			$criteria['elementId'] = craft()->request->getPost('elementId');
		}

		$type = craft()->request->getPost('type', 'subscriber');

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
			$errors = array(Craft::t('Unable to remove subscription.'));

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
}
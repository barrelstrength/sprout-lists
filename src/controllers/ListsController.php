<?php

namespace barrelstrength\sproutlists\controllers;

use barrelstrength\sproutbase\contracts\sproutlists\SproutListsBaseListType;
use barrelstrength\sproutlists\elements\Lists;
use barrelstrength\sproutlists\SproutLists;
use craft\web\Controller;
use Craft;

class ListsController extends Controller
{
    /**
     * Allow users who are not logged in to subscribe and unsubscribe from lists
     *
     * @var array
     */
    protected $allowAnonymous = ['actionSubscribe', 'actionUnsubscribe'];

    /**
     * Prepare variables for the List Edit Template
     *
     * @param array $variables
     *
     * @return null
     */
    public function actionEditListTemplate($type = null, $listId = null, $list = null)
    {
        $subscriberNamespace = 'barrelstrength\sproutlists\integrations\sproutlists\SubscriberListType';

        $type = isset($type) ? $type : $subscriberNamespace;

        $listType = SproutLists::$app->lists->getListType($type);

        if ($list == null)
        {
            $list = new Lists();
        }

        $continueEditingUrl = null;

        if ($list == null AND $listId != null) {

            /**
             * @var $listType SproutListsBaseListType
             */
            $list = $listType->getListById($listId);

            $continueEditingUrl = 'sprout-lists/lists/edit/'.$listId;
        }

        return $this->renderTemplate('sprout-lists/lists/_edit', [
            'listId' => $listId,
            'list'   => $list,
            'continueEditingUrl' => $continueEditingUrl
        ]);
    }


    /**
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveList()
    {
        $this->requirePostRequest();

        $list = new Lists();
        $list->id     = Craft::$app->request->getBodyParam('listId');
        $list->type   = Craft::$app->request->getBodyParam('type', 'subscriber');
        $list->name   = Craft::$app->request->getBodyParam('name');
        $list->handle = Craft::$app->request->getBodyParam('handle');

        $listType = sproutLists()->lists->getListType($list->type);

        if ($listType->saveList($list)) {
            craft()->userSession->setNotice(Craft::t('List saved.'));

            $this->redirectToPostedUrl();
        } else {
            craft()->userSession->setError(Craft::t('Unable to save list.'));

            craft()->urlManager->setRouteVariables([
                'list' => $list
            ]);
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

        $listId = craft()->request->getRequiredPost('listId');

        if (sproutLists()->lists->deleteList($listId)) {
            if (craft()->request->isAjaxRequest()) {
                $this->returnJson([
                    'success' => true
                ]);
            } else {
                craft()->userSession->setNotice(Craft::t('List deleted.'));

                $this->redirectToPostedUrl();
            }
        } else {
            if (craft()->request->isAjaxRequest()) {
                $this->returnJson([
                    'success' => false
                ]);
            } else {
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
        $subscription = new SproutLists_SubscriptionModel();
        $subscription->listType = craft()->request->getPost('listType', 'subscriber');
        $subscription->listHandle = craft()->request->getPost('listHandle');
        $subscription->listId = craft()->request->getPost('listId');
        $subscription->userId = craft()->request->getPost('userId');
        $subscription->email = craft()->request->getPost('email');
        $subscription->elementId = craft()->request->getPost('elementId');

        $listType = sproutLists()->lists->getListType($subscription->listType);

        if ($listType->subscribe($subscription)) {
            if (craft()->request->isAjaxRequest()) {
                $this->returnJson([
                    'success' => true,
                ]);
            } else {
                $this->redirectToPostedUrl();
            }
        } else {
            $errors = [Craft::t('Unable to save subscription.')];

            if (craft()->request->isAjaxRequest()) {
                $this->returnJson([
                    'errors' => $errors,
                ]);
            } else {
                craft()->urlManager->setRouteVariables([
                    'errors' => $errors
                ]);

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
        $subscription = new SproutLists_SubscriptionModel();
        $subscription->listType = craft()->request->getPost('listType', 'subscriber');
        $subscription->listHandle = craft()->request->getPost('listHandle');
        $subscription->listId = craft()->request->getPost('listId');
        $subscription->userId = craft()->request->getPost('userId');
        $subscription->email = craft()->request->getPost('email');
        $subscription->elementId = craft()->request->getPost('elementId');

        $listType = sproutLists()->lists->getListType($subscription->listType);

        if ($listType->unsubscribe($subscription)) {
            if (craft()->request->isAjaxRequest()) {
                $this->returnJson([
                    'success' => true,
                ]);
            } else {
                $this->redirectToPostedUrl();
            }
        } else {
            $errors = [Craft::t('Unable to remove subscription.')];

            if (craft()->request->isAjaxRequest()) {
                $this->returnJson([
                    'errors' => $errors,
                ]);
            } else {
                craft()->urlManager->setRouteVariables([
                    'errors' => $errors
                ]);

                $this->redirectToPostedUrl();
            }
        }
    }
}
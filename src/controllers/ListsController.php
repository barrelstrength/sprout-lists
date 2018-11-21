<?php

namespace barrelstrength\sproutlists\controllers;

use barrelstrength\sproutbase\app\lists\base\ListType;
use barrelstrength\sproutlists\elements\Lists;
use barrelstrength\sproutlists\listtypes\SubscriberListType;
use barrelstrength\sproutlists\models\Subscription;
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
     * @param null $type
     * @param null $listId
     * @param null $list
     *
     * @return \yii\web\Response
     * @throws \Exception
     */
    public function actionEditListTemplate($type = null, $listId = null, $list = null)
    {
        $type = $type !== null ? $type : SubscriberListType::class;

        $listType = SproutLists::$app->lists->getListType($type);

        if ($list == null) {
            $list = new Lists();
        }

        $continueEditingUrl = null;

        if ($listId != null) {

            /**
             * @var $listType ListType
             */
            $list = $listType->getListById($listId);

            $continueEditingUrl = 'sprout-lists/lists/edit/'.$listId;
        }

        return $this->renderTemplate('sprout-base-lists/lists/_edit', [
            'listId' => $listId,
            'list' => $list,
            'continueEditingUrl' => $continueEditingUrl
        ]);
    }

    /**
     * Saves a list
     *
     * @return null
     * @throws \Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveList()
    {
        $this->requirePostRequest();

        $listId = Craft::$app->request->getBodyParam('listId');

        $list = new Lists();

        if ($listId != null) {
            $list = Craft::$app->getElements()->getElementById($listId);
        }

        $list->name = Craft::$app->request->getBodyParam('name');
        $list->handle = Craft::$app->request->getBodyParam('handle');
        $list->type = Craft::$app->request->getRequiredBodyParam('type');

        /**
         * @var $listType ListType
         */
        $listType = SproutLists::$app->lists->getListType($list->type);
        $list->type = get_class($listType);
        $session = Craft::$app->getSession();

        if ($session && $listType->saveList($list)) {
            $session->setNotice(Craft::t('sprout-lists', 'List saved.'));

            $this->redirectToPostedUrl();
        } else {
            $session->setError(Craft::t('sprout-lists', 'Unable to save list.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'list' => $list
            ]);
        }

        return null;
    }

    /**
     * Deletes a list
     *
     * @return \yii\web\Response
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDeleteList()
    {
        $this->requirePostRequest();

        $listId = Craft::$app->getRequest()->getBodyParam('listId');
        $session = Craft::$app->getSession();

        if ($session && SproutLists::$app->lists->deleteList($listId)) {
            if (Craft::$app->getRequest()->getIsAjax()) {
                return $this->asJson([
                    'success' => true
                ]);
            }

            $session->setNotice(Craft::t('sprout-lists', 'List deleted.'));

            return $this->redirectToPostedUrl();
        }

        if (Craft::$app->getRequest()->getIsAjax()) {
            return $this->asJson([
                'success' => false
            ]);
        }

        $session->setError(Craft::t('sprout-lists', 'Unable to delete list.'));

        return $this->redirectToPostedUrl();
    }

    /**
     * Adds a subscriber to a list
     *
     * @return \yii\web\Response
     * @throws \Throwable
     * @throws \yii\base\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSubscribe()
    {
        $this->requirePostRequest();

        $subscription = new Subscription();
        $subscription->listHandle = Craft::$app->getRequest()->getRequiredBodyParam('listHandle');
        $subscription->listId = Craft::$app->getRequest()->getBodyParam('listId');
        $subscription->userId = Craft::$app->getRequest()->getBodyParam('userId');
        $subscription->email = Craft::$app->getRequest()->getBodyParam('email');
        $subscription->elementId = Craft::$app->getRequest()->getBodyParam('elementId');

        $listType = SproutLists::$app->lists->getListTypeByHandle($subscription->listHandle);

        $subscription->listType = get_class($listType);

        if ($listType->subscribe($subscription)) {
            if (Craft::$app->getRequest()->getIsAjax()) {
                return $this->asJson([
                    'success' => true,
                ]);
            }

            return $this->redirectToPostedUrl();
        }

        return Craft::$app->getUrlManager()->setRouteParams([
            'subscription' => $subscription
        ]);
    }

    /**
     * Removes a subscriber from a list
     *
     * @return \yii\web\Response
     * @throws \Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionUnsubscribe()
    {
        $this->requirePostRequest();

        $subscription = new Subscription();
        $subscription->listHandle = Craft::$app->getRequest()->getBodyParam('listHandle');
        $subscription->listId = Craft::$app->getRequest()->getBodyParam('listId');
        $subscription->userId = Craft::$app->getRequest()->getBodyParam('userId');
        $subscription->email = Craft::$app->getRequest()->getBodyParam('email');
        $subscription->elementId = Craft::$app->getRequest()->getBodyParam('elementId');

        $listType = SproutLists::$app->lists->getListTypeByHandle($subscription->listHandle);

        $subscription->listType = get_class($listType);

        if ($listType->unsubscribe($subscription)) {
            if (Craft::$app->getRequest()->getIsAjax()) {
                return $this->asJson([
                    'success' => true,
                ]);
            }

            return $this->redirectToPostedUrl();
        }

        $errors = [Craft::t('sprout-lists', 'Unable to remove subscription.')];

        if (Craft::$app->getRequest()->getIsAjax()) {
            return $this->asJson([
                'errors' => $errors,
            ]);
        }

        Craft::$app->getUrlManager()->setRouteParams([
            'errors' => $errors
        ]);

        return $this->redirectToPostedUrl();
    }
}
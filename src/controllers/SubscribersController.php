<?php

namespace barrelstrength\sproutlists\controllers;

use barrelstrength\sproutbaselists\base\ListType;
use barrelstrength\sproutlists\elements\Subscriber;
use barrelstrength\sproutlists\listtypes\SubscriberListType;
use barrelstrength\sproutlists\SproutLists;
use craft\web\Controller;
use Craft;
use yii\web\Response;

class SubscribersController extends Controller
{
    /**
     * Prepare variables for Subscriber Edit Template
     *
     * @param null $id
     * @param null $subscriber
     *
     * @return Response
     * @throws \Exception
     */
    public function actionEditSubscriberTemplate($id = null, $subscriber = null): Response
    {
        $listType = SproutLists::$app->lists->getListType(SubscriberListType::class);
        $listTypes[] = $listType;

        if ($id != null AND $subscriber == null) {
            $subscriber = $listType->getSubscriberById($id);
        }

        return $this->renderTemplate('sprout-base-lists/subscribers/_edit', [
            'subscriber' => $subscriber,
            'listTypes' => $listTypes
        ]);
    }

    /**
     * Saves a subscriber
     *
     * @return Response
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\base\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveSubscriber(): Response
    {
        $this->requirePostRequest();

        $subscriberId = Craft::$app->getRequest()->getBodyParam('subscriberId');

        $subscriber = new Subscriber();

        if ($subscriberId != null) {
            $subscriber = Craft::$app->getElements()->getElementById($subscriberId);
        }

        $subscriber->email = Craft::$app->getRequest()->getBodyParam('email');
        $subscriber->firstName = Craft::$app->getRequest()->getBodyParam('firstName');
        $subscriber->lastName = Craft::$app->getRequest()->getBodyParam('lastName');
        $subscriber->subscriberLists = Craft::$app->getRequest()->getBodyParam('sproutlists.subscriberLists');

        $type = Craft::$app->getRequest()->getBodyParam('type');

        /**
         * @todo - Abstract to add support for saveSubscriber via other ListTypes
         *
         * @var ListType|SubscriberListType $listType
         */
        $listType = SproutLists::$app->lists->getListType($type);

        $listType->cpBeforeSaveSubscriber($subscriber);

        if (!$listType->saveSubscriber($subscriber)) {
            Craft::$app->getSession()->setError(Craft::t('sprout-lists', 'Unable to save subscriber.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'subscriber' => $subscriber
            ]);
        }

        Craft::$app->getSession()->setNotice(Craft::t('sprout-lists', 'Subscriber saved.'));

        return $this->redirectToPostedUrl($subscriber);
    }

    /**
     * Deletes a subscriber
     *
     * @return Response
     * @throws \Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDeleteSubscriber(): Response
    {
        $this->requirePostRequest();

        $subscriberId = Craft::$app->getRequest()->getBodyParam('subscriberId');
        $listTypeParam = Craft::$app->getRequest()->getBodyParam('type');
        $listType = SproutLists::$app->lists->getListType($listTypeParam);

        // @todo - Refactor what we expect back in this method
        $subscriber = $listType->deleteSubscriberById($subscriberId);

        if (!$subscriber) {
            Craft::$app->getSession()->setError(Craft::t('sprout-lists', 'Unable to delete subscriber.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'subscriber' => $subscriber
            ]);
        }

        Craft::$app->getSession()->setNotice(Craft::t('sprout-lists', 'Subscriber deleted.'));

        return $this->redirectToPostedUrl();
    }
}
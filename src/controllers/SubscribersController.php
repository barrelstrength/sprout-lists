<?php

namespace barrelstrength\sproutlists\controllers;

use barrelstrength\sproutlists\elements\Subscribers;
use barrelstrength\sproutlists\SproutLists;
use craft\web\Controller;
use Craft;

class SubscribersController extends Controller
{
    /**
     * Prepare variables for Subscriber Edit Template
     * @param null $id
     * @param null $subscriber
     *
     * @return \yii\web\Response
     * @throws \Exception
     */
    public function actionEditSubscriberTemplate($id = null, $subscriber = null)
    {
        $subscriberNamespace = 'barrelstrength\sproutlists\integrations\sproutlists\SubscriberListType';
        $listType = SproutLists::$app->lists->getListType($subscriberNamespace);
        $listTypes[] = $listType;

        if ($id != null) {
            $subscriber = $listType->getSubscriberById($id);
        }

        return $this->renderTemplate('sprout-lists/subscribers/_edit', [
            'subscriber' => $subscriber,
            'listTypes' => $listTypes
        ]);
    }

    /**
     *  Saves a subscriber
     * @throws \Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveSubscriber()
    {
        $this->requirePostRequest();

        $subscriber                  = new Subscribers();
        $subscriber->id              = Craft::$app->getRequest()->getBodyParam('subscriberId');
        $subscriber->email           = Craft::$app->getRequest()->getBodyParam('email');
        $subscriber->firstName       = Craft::$app->getRequest()->getBodyParam('firstName');
        $subscriber->lastName        = Craft::$app->getRequest()->getBodyParam('lastName');
        $subscriber->subscriberLists = Craft::$app->getRequest()->getBodyParam('sproutlists.subscriberLists');

        $type = Craft::$app->getRequest()->getBodyParam('type');

        $listType = SproutLists::$app->lists->getListType($type);

        $session = Craft::$app->getSession();

        if ($session AND $listType->saveSubscriber($subscriber)) {
            $session->setNotice(Craft::t('sprout-lists', 'Subscriber saved.'));

            return $this->redirectToPostedUrl($subscriber);
        }

        $session->setError(Craft::t('sprout-lists','Unable to save subscriber.'));

        return Craft::$app->getUrlManager()->setRouteParams([
            'subscriber' => $subscriber
        ]);
    }

    /**
     * Deletes a subscriber
     *
     * @return null
     */
    public function actionDeleteSubscriber()
    {
        $this->requirePostRequest();

        $subscriberId = craft()->request->getRequiredPost('subscriberId');

        $listType = craft()->request->getRequiredPost('type');

        if ($subscriber = $listType->deleteSubscriberById($subscriberId)) {
            craft()->userSession->setNotice(Craft::t('Subscriber deleted.'));

            $this->redirectToPostedUrl($subscriber);
        } else {
            craft()->userSession->setError(Craft::t('Unable to delete subscriber.'));

            craft()->urlManager->setRouteVariables([
                'subscriber' => $subscriber
            ]);
        }
    }
}
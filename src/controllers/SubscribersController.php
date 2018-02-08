<?php

namespace barrelstrength\sproutlists\controllers;

use barrelstrength\sproutlists\elements\Subscribers;
use barrelstrength\sproutlists\SproutLists;
use craft\web\Controller;

class SubscribersController extends Controller
{
    /**
     * Prepare variables for Subscriber Edit Template
     *
     * @param array $variables
     *
     * @return null
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
     * Saves a subscriber
     *
     * @return null
     */
    public function actionSaveSubscriber()
    {
        $this->requirePostRequest();

        $subscriber = new SproutLists_SubscriberModel();
        $subscriber->id = craft()->request->getPost('subscriberId');
        $subscriber->email = craft()->request->getRequiredPost('email');
        $subscriber->firstName = craft()->request->getPost('firstName');
        $subscriber->lastName = craft()->request->getPost('lastName');
        $subscriber->subscriberLists = craft()->request->getPost('sproutlists.subscriberLists');

        $type = craft()->request->getRequiredPost('type');

        $listType = sproutLists()->lists->getListType($type);

        if ($listType->saveSubscriber($subscriber)) {
            craft()->userSession->setNotice(Craft::t('Subscriber saved.'));

            $this->redirectToPostedUrl($subscriber);
        } else {
            craft()->userSession->setError(Craft::t('Unable to save subscriber.'));

            craft()->urlManager->setRouteVariables([
                'subscriber' => $subscriber
            ]);
        }
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
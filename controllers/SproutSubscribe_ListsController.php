<?php
namespace Craft;

/**
 * Lists controller
 *
 */
class SproutSubscribe_ListsController extends BaseController
{

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
                'response' => 'success',
            ));
            } else {
                $this->redirectToPostedUrl();
            }
        } else {
            $errors = $record->getErrors();

            if (craft()->request->isAjaxRequest())
            {
                $this->returnJson(array(
                    'response' => $errors,
                ));
            } else {
                craft()->urlManager->setRouteVariables(array(
                    'response' => $errors
                ));

                $this->redirectToPostedUrl();
            }
        }
    }

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
                    'response' => 'success',
                ));
            } else {
                $this->redirectToPostedUrl();
            }
        } else {
            if (craft()->request->isAjaxRequest())
            {
                $this->returnJson(array(
                    'response' => 'fail',
                ));
            } else {
                craft()->urlManager->setRouteVariables(array(
                    'response' => 'fail',
                ));

                $this->redirectToPostedUrl();
            }
        }

        $this->redirectToPostedUrl();
    }

}
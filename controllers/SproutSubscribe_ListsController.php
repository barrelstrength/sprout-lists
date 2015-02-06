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

    if (!$userId OR !$elementId)
    {
      return false;
    }

    $record = new SproutSubscribe_SubscriptionRecord;
    $record->userId = $userId;
    $record->elementId = $elementId;

    if ($record->save())
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

    $result = craft()->db->createCommand()
               ->delete('sproutsubscribe_subscriptions', array(
                  'userId' => $userId,
                  'elementId' => $elementId
               ));

    if ($result)
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

    $this->redirectToPostedUrl();
  }
}
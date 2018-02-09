<?php

namespace barrelstrength\sproutlists\services;

use craft\base\Component;
use yii\base\Event;
use barrelstrength\sproutlists\records\Subscribers as SubscribersRecord;
use barrelstrength\sproutlists\elements\Subscribers as SubscribersElement;
use Craft;


class Subscribers extends Component
{
    /**
     * Sync SproutLists subscriber to craft_users if same email is found on save.
     *
     * @param Event $event
     *
     * @return bool
     * @throws \Exception
     */
    /**
     * @param Event $event
     *
     * @return SubscribersElement|bool
     * @throws \Exception
     * @throws \Throwable
     */
    public function updateUserIdOnSave(Event $event)
    {
        $userId = $event->params['user']->id;
        $email = $event->params['user']->email;

        $subscriberRecord = SubscribersRecord::find()->where(['userId' => $userId])->one();

        // If that doesn't work, try to find a user with a matching email address
        if ($subscriberRecord == null) {

            $subscriberRecord = SubscribersRecord::find()
                ->where(['userId' => $userId, 'email' => $email])
                ->one();

            if ($subscriberRecord) {
                // Assign the user ID to the subscriber with the matching email address
                $subscriberRecord->userId = $event->params['user']->id;
            }
        }

        if ($subscriberRecord != null) {
            // If the user has updated their email, let's also update it for our Subscriber
            $subscriberRecord->email = $event->params['user']->email;
            $subscriberRecord->firstName = $event->params['user']->firstName;
            $subscriberRecord->lastName = $event->params['user']->lastName;

            $subscriberElement = new SubscribersElement;
            $subscriberElement->setAttributes($subscriberRecord->getAttributes());

            try {
                if (Craft::$app->getElements()->saveElement($subscriberElement)) {
                    return $subscriberElement;
                }
            } catch (\Exception $e) {
                throw $e;
            }
        }

        return false;
    }

    /**
     * Remove any relationships between Sprout Lists Subscribers and Users who are deleted
     *
     * @param Event $event
     *
     * @return bool
     * @throws \Exception
     */
    public function updateUserIdOnDelete(Event $event)
    {
        $userId = $event->params['user']->id;

        $subscriberElement = SubscribersElement::find()->where([
            'userId' => $userId,
        ])->one();

        if ($subscriberElement != null) {
            $subscriberElement->userId = null;

        }

        return false;
    }
}
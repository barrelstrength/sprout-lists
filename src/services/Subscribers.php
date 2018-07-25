<?php

namespace barrelstrength\sproutlists\services;

use barrelstrength\sproutlists\records\Subscription;

use craft\base\Component;
use craft\elements\User;
use craft\events\ElementEvent;

use barrelstrength\sproutlists\records\Subscribers as SubscribersRecord;
use barrelstrength\sproutlists\elements\Subscribers as SubscribersElement;
use Craft;


class Subscribers extends Component
{
    /**
     * Sync SproutLists subscriber to craft_users if same email is found on save.
     *
     * @param ElementEvent $event
     *
     * @return SubscribersElement|bool
     * @throws \Exception
     * @throws \Throwable
     */
    public function updateUserIdOnSave(ElementEvent $event)
    {
        /**
         * @var User $user
         */
        $user = $event->element;

        /**
         * @var SubscribersRecord $subscriberRecord
         */
        $subscriberRecord = SubscribersRecord::find()->where([
            'userId' => $user->id
        ])->one();

        // If that doesn't work, try to find a user with a matching email address
        if ($subscriberRecord === null) {

            $subscriberRecord = SubscribersRecord::find()
                ->where([
                    'userId' => null,
                    'email' => $user->email
                ])
                ->one();

            if ($subscriberRecord) {
                // Assign the user ID to the subscriber with the matching email address
                $subscriberRecord->userId = $user->id;
            }
        }

        if ($subscriberRecord !== null) {

            // Sync updates with existing Craft User if User Sync enabled
            $subscriberRecord->email = $user->email;
            $subscriberRecord->firstName = $user->firstName;
            $subscriberRecord->lastName = $user->lastName;

            try {

                $subscriberRecord->update(false);

                return true;
            } catch (\Exception $e) {
                throw $e;
            }
        }

        return false;
    }

    /**
     * Remove any relationships between Sprout Lists Subscribers and Users who are deleted.
     * Deleting a Craft User does not delete the matching Subscriber. It simply removes
     * the relationship to any Craft User ID from the Subscriber table.
     *
     * @param ElementEvent $event
     *
     * @return bool
     * @throws \Exception
     * @throws \Throwable
     */
    public function updateUserIdOnDelete(ElementEvent $event)
    {
        /**
         * @var $user User
         */
        $user = $event->element;

        /**
         * @var SubscribersRecord $subscriberRecord
         */
        $subscriberRecord = SubscribersRecord::find()->where([
            'userId' => $user->id,
        ])->one();

        if ($subscriberRecord !== null) {

            $subscriberRecord->userId = null;

            try {
                $subscriberRecord->save();

                return true;
            } catch (\Exception $e) {
                throw $e;
            }
        }

        return false;
    }

    /**
     * Delete a subscriber and all related subscriptions
     *
     * @param $id
     *
     * @throws \Throwable
     */
    public function deleteSubscriberById($id)
    {
        if (Craft::$app->getElements()->deleteElementById($id)) {
            SubscribersRecord::deleteAll('id = :subscriberId', [':subscriberId' => $id]);
            Subscription::deleteAll('subscriberId = :subscriberId', [':subscriberId' => $id]);
        }
    }
}
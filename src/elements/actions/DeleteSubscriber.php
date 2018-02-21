<?php

namespace barrelstrength\sproutlists\elements\actions;

use Craft;
use craft\elements\actions\Delete;
use craft\elements\db\ElementQueryInterface;
use barrelstrength\sproutlists\records\Subscribers as SubscribersRecord;

/**
 * Class DeleteSubscriber
 *
 * @package barrelstrength\sproutlists\elements\actions
 */
class DeleteSubscriber extends Delete
{
    /**
     * @var string|null The confirmation message that should be shown before the elements get deleted
     */
    public $confirmationMessage = 'Are you sure you want to delete this subscriber(s)?';

    /**
     * @var string|null The message that should be shown after the elements get deleted
     */
    public $successMessage = 'Subscriber(s) deleted.';

    /**
     *  Performs the action on any elements that match the given criteria.
     *  return Whether the action was performed successfully.
     *
     * @param ElementQueryInterface $query
     *
     * @return bool
     * @throws \Throwable
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        $subscribers = $query->all();

        // Delete the users
        foreach ($subscribers as $subscriber) {
            $id = $subscriber->id;

            $result = Craft::$app->getElements()->deleteElement($subscriber);

            if ($result) {
                $record = SubscribersRecord::findOne($id);
                if ($record) {
                    $record->delete();
                }
            }
        }

        $this->setMessage(Craft::t('app', 'Subscriber(s) deleted.'));

        return true;
    }
}

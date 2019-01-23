<?php

namespace barrelstrength\sproutlists\elements\actions;

use barrelstrength\sproutlists\elements\Subscriber;
use barrelstrength\sproutlists\listtypes\SubscriberListType;
use barrelstrength\sproutlists\SproutLists;
use Craft;
use craft\elements\actions\Delete;
use craft\elements\db\ElementQueryInterface;

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
        /**
         * @var Subscriber[] $subscribers
         */
        $subscribers = $query->all();

        $listType = SproutLists::$app->lists->getListType(SubscriberListType::class);

        // Delete the users
        foreach ($subscribers as $subscriber) {
            $id = $subscriber->id;

            $listType->deleteSubscriberById($id);
        }

        $this->setMessage(Craft::t('sprout-lists', 'Subscriber(s) deleted.'));

        return true;
    }
}

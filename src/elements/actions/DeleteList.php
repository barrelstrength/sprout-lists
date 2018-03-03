<?php

namespace barrelstrength\sproutlists\elements\actions;

use barrelstrength\sproutlists\SproutLists;
use Craft;
use craft\elements\actions\Delete;
use craft\elements\db\ElementQueryInterface;

/**
 * Class DeleteList
 *
 * @package barrelstrength\sproutlists\elements\actions
 */
class DeleteList extends Delete
{
    /**
     * @var string|null The confirmation message that should be shown before the elements get deleted
     */
    public $confirmationMessage = 'Are you sure you want to delete this list(s)?';

    /**
     * @var string|null The message that should be shown after the elements get deleted
     */
    public $successMessage = 'List(s) deleted.';

    /**
     * @param ElementQueryInterface $query
     *
     * @return bool
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        $lists = $query->all();
        // Delete the users
        foreach ($lists as $list) {
            $id = $list->id;

            SproutLists::$app->lists->deleteList($id);
        }

        $this->setMessage(Craft::t('sprout-lists', 'List(s) deleted.'));

        return true;
    }
}

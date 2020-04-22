<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutlists\elements\actions;

use barrelstrength\sproutlists\elements\ListElement;
use barrelstrength\sproutlists\SproutLists;
use Craft;
use craft\elements\actions\Delete;
use craft\elements\db\ElementQueryInterface;
use Exception;
use Throwable;

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
     * @throws Exception
     * @throws Throwable
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        /**
         * @var ListElement[] $lists
         */
        $lists = $query->all();

        // Delete the users
        foreach ($lists as $list) {
            $listType = SproutLists::$app->lists->getListType($list->type);
            $listType->deleteList($list);
        }

        $this->setMessage(Craft::t('sprout-lists', 'List(s) deleted.'));

        return true;
    }
}

<?php /**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

/** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutlists\migrations;

use craft\db\Migration;
use craft\db\Table;

class m211112_000000_update_element_types extends Migration
{
    /**
     * @return bool
     */
    public function safeUp(): bool
    {
        $types = [
            [
                'oldType' => 'barrelstrength\sproutbaselists\elements\Subscriber',
                'newType' => 'barrelstrength\sproutlists\elements\Subscriber',
            ],
            [
                'oldType' => 'barrelstrength\sproutbaselists\elements\ListElement',
                'newType' => 'barrelstrength\sproutlists\elements\ListElement',
            ],
        ];

        foreach ($types as $type) {
            $this->update(Table::ELEMENTS, [
                'type' => $type['newType'],
            ], ['type' => $type['oldType']], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m211112_000000_update_element_types cannot be reverted.\n";

        return false;
    }
}

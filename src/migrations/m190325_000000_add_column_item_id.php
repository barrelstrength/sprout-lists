<?php

namespace barrelstrength\sproutlists\migrations;

use craft\db\Migration;

/**
 * Class m190123_000000_rename_elements
 */
class m190325_000000_add_column_item_id extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {

        $table = '{{%sproutlists_subscriptions}}';

        if (!$this->db->columnExists($table, 'itemId')) {
            $this->addColumn($table, 'itemId', $this->string()->after('subscriberId'));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190325_000000_add_column_item_id cannot be reverted.\n";
        return false;
    }
}

<?php

namespace barrelstrength\sproutlists\migrations;

use craft\db\Migration;

/**
 * Class m190123_000000_remove_not_null
 */
class m190123_000000_remove_not_null extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->alterColumn('{{%sproutlists_lists}}', 'elementId', $this->integer());

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190123_000000_remove_not_null cannot be reverted.\n";
        return false;
    }
}

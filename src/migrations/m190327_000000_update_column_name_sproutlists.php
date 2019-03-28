<?php

namespace barrelstrength\sproutlists\migrations;

use barrelstrength\sproutbaselists\migrations\m190327_000000_update_column_name;
use craft\db\Migration;

/**
 * Class m190327_000000_update_subscription_column_name_sproutlists
 *
 * @package barrelstrength\sproutlists\migrations
 */
class m190327_000000_update_column_name_sproutlists extends Migration
{
    /**
     * @return bool
     * @throws \yii\base\NotSupportedException
     */
    public function safeUp(): bool
    {
        $migration = new m190327_000000_update_column_name();

        ob_start();
        $migration->safeUp();
        ob_end_clean();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190327_000000_update_subscription_column_name_sproutlists cannot be reverted.\n";
        return false;
    }
}

<?php

namespace barrelstrength\sproutlists\migrations;

use barrelstrength\sproutbaselists\migrations\m190403_000001_update_list_type;
use craft\db\Migration;

/**
 * Class m190403_000001_update_list_type_sproutlists
 *
 * @package barrelstrength\sproutlists\migrations
 */
class m190403_000001_update_list_type_sproutlists extends Migration
{
    /**
     * @return bool
     */
    public function safeUp(): bool
    {
        $migration = new m190403_000001_update_list_type();

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
        echo "m190403_000001_update_list_type_sproutlists cannot be reverted.\n";
        return false;
    }
}

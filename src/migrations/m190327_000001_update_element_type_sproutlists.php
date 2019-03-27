<?php

namespace barrelstrength\sproutlists\migrations;

use barrelstrength\sproutbaselists\migrations\m190327_000001_update_element_type;
use craft\db\Migration;

/**
 * Class m190327_000001_update_subscriber_element_type_sproutlists
 *
 * @package barrelstrength\sproutlists\migrations
 */
class m190327_000001_update_element_type_sproutlists extends Migration
{
    /**
     * @return bool
     * @throws \yii\base\NotSupportedException
     */
    public function safeUp(): bool
    {
        $migration = new m190327_000001_update_element_type();

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
        echo "m190327_000001_update_subscriber_element_type_sproutlists cannot be reverted.\n";
        return false;
    }
}

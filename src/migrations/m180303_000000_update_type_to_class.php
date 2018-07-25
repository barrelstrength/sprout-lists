<?php

namespace barrelstrength\sproutlists\migrations;

use barrelstrength\sproutlists\listtypes\SubscriberListType;
use craft\db\Migration;

/**
 * Class m180303_000000_update_type_to_class
 */
class m180303_000000_update_type_to_class extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Link to Url
        $this->update('{{%sproutlists_lists}}', [
            'type' => SubscriberListType::class
        ], [
            'type' => 'subscriber'
        ], [], false);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180303_000000_update_type_to_class cannot be reverted.\n";
        return false;
    }
}

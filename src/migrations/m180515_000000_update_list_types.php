<?php

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;

/**
 * m180515_000000_update_list_types migration.
 */
class m180515_000000_update_list_types extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $listClasses = [
            0 => [
                'oldType' => 'barrelstrength\sproutlists\integrations\sproutlists\SubscriberListType',
                'newType' => 'barrelstrength\sproutlists\listtypes\SubscriberListType'
            ]
        ];

        foreach ($listClasses as $listClass) {
            $this->update('{{%sproutlists_lists}}', [
                'type' => $listClass['newType']], ['type' => $listClass['oldType']], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180515_000000_update_list_types cannot be reverted.\n";
        return false;
    }
}

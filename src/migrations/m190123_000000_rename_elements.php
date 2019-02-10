<?php

namespace barrelstrength\sproutlists\migrations;

use craft\db\Migration;

/**
 * Class m190123_000000_rename_elements
 */
class m190123_000000_rename_elements extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        /** @noinspection ClassConstantCanBeUsedInspection */
        $types = [
            0 => [
                'oldType' => 'barrelstrength\sproutlists\elements\Lists',
                'newType' => 'barrelstrength\sproutlists\elements\SubscriberList'
            ],
            1 => [
                'oldType' => 'barrelstrength\sproutlists\elements\Subscribers',
                'newType' => 'barrelstrength\sproutlists\elements\Subscriber'
            ],
        ];

        foreach ($types as $type) {
            $this->update('{{%elements}}', [
                'type' => $type['newType']
            ], ['type' => $type['oldType']], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190123_000000_rename_elements cannot be reverted.\n";
        return false;
    }
}

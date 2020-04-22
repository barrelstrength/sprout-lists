<?php /**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

/** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutlists\migrations;

use craft\db\Migration;

class m200110_000000_update_subscriberlisttype_types extends Migration
{
    /**
     * @return bool
     */
    public function safeUp(): bool
    {
        $mailingClasses = [
            0 => [
                'oldType' => 'barrelstrength\sproutbaselists\listtypes\MailingList',
                'newType' => 'barrelstrength\sproutlists\listtypes\SubscriberList'
            ]
        ];

        foreach ($mailingClasses as $mailingClass) {
            $this->update('{{%sproutlists_lists}}', [
                'type' => $mailingClass['newType']
            ], ['type' => $mailingClass['oldType']], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200110_000000_update_subscriberlisttype_types cannot be reverted.\n";

        return false;
    }
}

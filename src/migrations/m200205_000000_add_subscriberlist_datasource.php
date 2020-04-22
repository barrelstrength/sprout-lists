<?php /**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

/** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutlists\migrations;

use craft\db\Migration;
use craft\db\Query;

class m200205_000000_add_subscriberlist_datasource extends Migration
{
    /**
     * @return bool
     */
    public function safeUp(): bool
    {
        /** @noinspection ClassConstantCanBeUsedInspection */
        $dataSourceClasses = [
            'barrelstrength\sproutlists\integrations\sproutreports\datasources\SubscriberListDataSource'
        ];

        $dataSourceTable = '{{%sproutreports_datasources}}';

        if ($this->db->tableExists($dataSourceTable)) {
            foreach ($dataSourceClasses as $dataSourceClass) {
                $dataSourceExists = (new Query())
                    ->select('id')
                    ->from([$dataSourceTable])
                    ->where(['type' => $dataSourceClass])
                    ->exists();

                if (!$dataSourceExists) {
                    $this->insert($dataSourceTable, [
                        'type' => $dataSourceClass,
                        'viewContext' => 'sprout-forms',
                        'allowNew' => 1
                    ]);
                }
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200205_000000_add_subscriberlist_datasource cannot be reverted.\n";

        return false;
    }
}

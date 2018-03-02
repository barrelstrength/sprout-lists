<?php

namespace barrelstrength\sproutlists\records;

use craft\db\ActiveRecord;

class Subscription extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return 'sproutlists_subscriptions';
    }
}

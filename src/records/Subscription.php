<?php

namespace barrelstrength\sproutlists\records;

use craft\base\Element;
use craft\db\ActiveRecord;
use yii\db\ActiveQueryInterface;

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

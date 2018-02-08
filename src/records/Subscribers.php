<?php

namespace barrelstrength\sproutlists\records;

use craft\base\Element;
use craft\db\ActiveRecord;
use yii\db\ActiveQueryInterface;

class Subscribers extends ActiveRecord
{
    public $subscriberLists;
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return 'sproutlists_subscribers';
    }

    /**
     * Returns the entry’s element.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getElement(): ActiveQueryInterface
    {
        return $this->hasOne(Element::class, ['id' => 'id']);
    }
}

<?php

namespace barrelstrength\sproutbase\records\sproutemail;

use craft\base\Element;
use craft\db\ActiveRecord;
use yii\db\ActiveQueryInterface;

class Lists extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return 'sproutlists_lists';
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

<?php

namespace barrelstrength\sproutlists\records;

use craft\base\Element;
use craft\db\ActiveRecord;
use yii\db\ActiveQueryInterface;

/**
 * Class Lists record.
 *
 * @property int    $id
 * @property int    $elementId
 * @property string $type
 * @property string $name
 * @property string $handle
 * @property int    $totalSubscribers
 */
class Lists extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%sproutlists_lists}}';
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

    /**
     * @return ActiveQueryInterface
     */
    public function getSubscribers(): ActiveQueryInterface
    {
        return $this->hasMany(Subscribers::class, ['id' => 'subscriberId'])
            ->viaTable('{{%sproutlists_subscriptions}}', ['listId' => 'id']);
    }
}

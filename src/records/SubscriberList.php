<?php

namespace barrelstrength\sproutlists\records;

use craft\base\Element;
use craft\db\ActiveRecord;
use yii\db\ActiveQueryInterface;

/**
 * Class SubscriberList record.
 *
 * @property int                          $id
 * @property int                          $elementId
 * @property string                       $type
 * @property string                       $name
 * @property string                       $handle
 * @property \yii\db\ActiveQueryInterface $element
 * @property \yii\db\ActiveQueryInterface $subscribers
 * @property int                          $totalSubscribers
 */
class SubscriberList extends ActiveRecord
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
        return $this->hasMany(Subscriber::class, ['id' => 'subscriberId'])
            ->viaTable('{{%sproutlists_subscriptions}}', ['listId' => 'id']);
    }
}

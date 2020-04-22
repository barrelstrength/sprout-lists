<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutlists\models;

use barrelstrength\sproutlists\base\ListType;
use barrelstrength\sproutlists\base\SubscriptionInterface;
use barrelstrength\sproutlists\records\Subscription as SubscriptionRecord;
use craft\base\Model;
use craft\validators\UniqueValidator;
use DateTime;

class Subscription extends Model implements SubscriptionInterface
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var ListType
     */
    public $listType;

    /**
     * @var string
     */
    public $listHandle;

    /**
     * @var
     */
    public $listId;

    /**
     * @var
     */
    public $elementId;

    /**
     * @var int
     */
    public $itemId;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $firstName;

    /**
     * @var string
     */
    public $lastName;

    /**
     * @var DateTime|null
     */
    public $dateCreated;

    /**
     * @var DateTime|null
     */
    public $dateUpdated;

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getListType(): ListType
    {
        return $this->listType;
    }

    /**
     * @return array
     */
    public function defineRules(): array
    {
        $rules = parent::defineRules();

        $rules[] = [
            ['email'],
            'required',
            'on' => [self::SCENARIO_SUBSCRIBER]
        ];
        $rules[] = [
            ['listId'],
            'required',
            'when' => static function() {
                return !self::SCENARIO_SUBSCRIBER;
            }
        ];
        $rules[] = [['email'], 'email'];
        $rules[] = [
            ['listId', 'itemId'],
            UniqueValidator::class,
            'targetClass' => SubscriptionRecord::class,
            'targetAttribute' => ['listId', 'itemId']
        ];

        return $rules;
    }
}

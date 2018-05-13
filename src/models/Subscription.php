<?php

namespace barrelstrength\sproutlists\models;

use barrelstrength\sproutbase\app\lists\base\ListType;
use craft\base\Model;
use DateTime;

class Subscription extends Model
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $listId;

    /**
     * @var int
     */
    public $subscriberId;

    /**
     * @var DateTime|null
     */
    public $dateCreated;

    /**
     * @var DateTime|null
     */
    public $dateUpdated;

    /**
     * @var ListType
     */
    public $listType;

    /**
     * @var string
     */
    public $listHandle;

    /**
     * @var int
     */
    public $elementId;

    /**
     * @var int
     */
    public $userId;

    /**
     * @var string
     */
    public $email;
}

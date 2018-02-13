<?php

namespace barrelstrength\sproutlists\models;

use craft\base\Model;
class Subscription extends Model
{
    public $id;
    public $listId;
    public $subscriberId;
    public $dateCreated;
    public $dateUpdated;
}

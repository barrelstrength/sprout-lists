<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutlists\web\twig\variables;

use barrelstrength\sproutlists\elements\db\ListElementQuery;
use barrelstrength\sproutlists\elements\db\SubscriberQuery;
use barrelstrength\sproutlists\elements\ListElement;
use barrelstrength\sproutlists\elements\Subscriber;
use Craft;

class SproutListsVariable
{
    /**
     * @param array $criteria
     *
     * @return ListElementQuery
     */
    public function lists(array $criteria = []): ListElementQuery
    {
        $query = ListElement::find();
        Craft::configure($query, $criteria);

        return $query;
    }

    /**
     * @param array $criteria
     *
     * @return SubscriberQuery
     */
    public function subscribers(array $criteria = []): SubscriberQuery
    {
        $query = Subscriber::find();
        Craft::configure($query, $criteria);

        return $query;
    }
}

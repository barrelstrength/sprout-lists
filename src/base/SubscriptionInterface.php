<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutlists\base;

interface SubscriptionInterface
{
    const SCENARIO_SUBSCRIBER = 'subscriber';

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return ListType
     */
    public function getListType(): ListType;
}
<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutlists\base;

interface SubscriberInterface
{
    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return string|null
     */
    public function getEmail();
}
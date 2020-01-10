<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbasereports\elements;

use Craft;

/**
 * Class Segment
 *
 * @package barrelstrength\sproutbasereports\elements
 */
class Segment extends Report
{
    /**
     * Returns the element type name.
     *
     * @return string
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-lists', 'Segments (Sprout)');
    }
}
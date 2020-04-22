<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutlists\models;

use barrelstrength\sproutbase\base\SproutSettingsInterface;
use Craft;
use craft\base\Model;

/**
 *
 * @property string $pluginNameOverride
 * @property bool   $enableUserSync
 * @property bool   $enableAutoList
 * @property array  $settingsNavItems
 */
class Settings extends Model implements SproutSettingsInterface
{
    /**
     * @var string
     */
    public $pluginNameOverride;

    /**
     * @var bool
     */
    public $enableUserSync;

    /**
     * @var bool
     */
    public $enableAutoList;

    /**
     * @inheritdoc
     */
    public function getSettingsNavItems(): array
    {
        return [
            'settingsHeading' => [
                'heading' => Craft::t('sprout-lists', 'Settings'),
            ],
            'general' => [
                'label' => Craft::t('sprout-lists', 'General'),
                'url' => 'sprout-lists/settings/general',
                'selected' => 'general',
                'template' => 'sprout-lists/settings/general'
            ]
        ];
    }
}

<?php

namespace barrelstrength\sproutlists\models;

use craft\base\Model;
use Craft;

/**
 *
 * @property string $pluginNameOverride
 * @property bool $enableUserSync
 * @property bool $enableAutoList
 * @property array $settingsNavItems
 */
class Settings extends Model
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
                'template' => 'sprout-base-lists/settings/general'
            ]
        ];
    }
}

<?php

namespace barrelstrength\sproutlists\models;

use craft\base\Model;
use Craft;

class Settings extends Model
{
    public $enableUserSync;

    public function getSettingsNavItems()
    {
        return [
            'settingsHeading' => [
                'heading' => Craft::t('sprout-lists', 'Settings'),
            ],
            'general' => [
                'label' => Craft::t('sprout-lists', 'General'),
                'url' => 'sprout-lists/settings/general',
                'selected' => 'general',
                'template' => 'sprout-lists/_settings/general'
            ]
        ];
    }
}

<?php

namespace barrelstrength\sproutlists;

use barrelstrength\sproutbase\base\BaseSproutTrait;
use barrelstrength\sproutbase\SproutBaseHelper;
use barrelstrength\sproutbaselists\SproutBaseListsHelper;
use barrelstrength\sproutbaselists\models\Settings;
use barrelstrength\sproutlists\integrationtypes\SproutListsIntegration;
use barrelstrength\sproutbasereports\services\DataSources;
use barrelstrength\sproutlists\integrations\sproutreports\datasources\CustomMailingListQuery;
use craft\base\Plugin;
use Craft;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\helpers\UrlHelper;
use craft\services\UserPermissions;
use craft\web\UrlManager;
use yii\base\Event;

/**
 * Class SproutListsPlugin
 *
 * @package Craft
 *
 * @property mixed $cpNavItem
 * @property array $userPermissions
 * @property array $cpUrlRules
 * @property mixed $settingsResponse
 */
class SproutLists extends Plugin
{
    use BaseSproutTrait;

    /**
     * @var string
     */
    public static $pluginHandle = 'sprout-lists';

    /**
     * @var bool
     */
    public $hasCpSection = true;

    /**
     * @var bool
     */
    public $hasCpSettings = true;

    /**
     * @var string
     */
    public $schemaVersion = '4.0.5';

    /**
     * @var string
     */
    public $minVersionRequired = '0.7.1';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        SproutBaseHelper::registerModule();
        SproutBaseListsHelper::registerModule();

        Craft::setAlias('@sproutlists', $this->getBasePath());

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, $this->getCpUrlRules());
        });

        Event::on(UserPermissions::class, UserPermissions::EVENT_REGISTER_PERMISSIONS, function(RegisterUserPermissionsEvent $event) {
            $event->permissions['Sprout Lists'] = $this->getUserPermissions();
        });
    }

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * Redirect to Sprout SubscriberList settings
     *
     * @inheritdoc
     */
    public function getSettingsResponse()
    {
        $url = UrlHelper::cpUrl('sprout-lists/settings');

        return Craft::$app->getResponse()->redirect($url);
    }

    /**
     * @inheritdoc
     */
    public function getCpNavItem()
    {
        $parent = parent::getCpNavItem();

        // Allow user to override plugin name in sidebar
        if ($this->getSettings()->pluginNameOverride) {
            $parent['label'] = $this->getSettings()->pluginNameOverride;
        }

        if (Craft::$app->getUser()->checkPermission('sproutLists-editSubscribers')) {
            $parent['subnav']['subscribers'] = [
                'label' => Craft::t('sprout-lists', 'Subscribers'),
                'url' => 'sprout-lists/subscribers'
            ];
        }
        if (Craft::$app->getUser()->checkPermission('sproutLists-editLists')) {
            $parent['subnav']['lists'] = [
                'label' => Craft::t('sprout-lists', 'Lists'),
                'url' => 'sprout-lists/lists'
            ];
        }

        if (Craft::$app->getUser()->getIsAdmin()) {
            $parent['subnav']['settings'] = [
                'label' => Craft::t('sprout-lists', 'Settings'),
                'url' => 'sprout-lists/settings/general'
            ];
        }

        return $parent;
    }

    private function getCpUrlRules(): array
    {
        return [
            'sprout-lists' =>
                'sprout-base-lists/lists/lists-index-template',

            // Subscribers
            'sprout-lists/subscribers/new' =>
                'sprout-base-lists/subscribers/edit-subscriber-template',
            'sprout-lists/subscribers/edit/<id:\d+>' =>
                'sprout-base-lists/subscribers/edit-subscriber-template',
            'sprout-lists/subscribers/<listHandle:.*>' => [
                'template' => 'sprout-base-lists/subscribers'
            ],
            'sprout-lists/subscribers' =>
                'sprout-base-lists/subscribers/subscribers-index-template',

            // Lists
            'sprout-lists/lists' =>
                'sprout-base-lists/lists/lists-index-template',
            'sprout-lists/lists/new' =>
                'sprout-base-lists/lists/list-edit-template',
            'sprout-lists/lists/edit/<listId:\d+>' =>
                'sprout-base-lists/lists/list-edit-template',

            // Settings
            'sprout-lists/settings' =>
                'sprout/settings/edit-settings',
            'sprout-lists/settings/<settingsSectionHandle:.*>' =>
                'sprout/settings/edit-settings',
        ];
    }

    /**
     * @return array
     */
    public function getUserPermissions(): array
    {
        return [
            'sproutLists-editSubscribers' => [
                'label' => Craft::t('sprout-lists', 'Edit Subscribers')
            ],
            'sproutLists-editLists' => [
                'label' => Craft::t('sprout-lists', 'Edit Lists')
            ]
        ];
    }
}

<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutlists;

use barrelstrength\sproutbase\SproutBaseHelper;
use barrelstrength\sproutbasereports\base\DataSource;
use barrelstrength\sproutbasereports\services\DataSources;
use barrelstrength\sproutbasereports\SproutBaseReports;
use barrelstrength\sproutlists\events\RegisterListTypesEvent;
use barrelstrength\sproutlists\integrations\sproutreports\datasources\SubscriberListDataSource;
use barrelstrength\sproutlists\listtypes\SubscriberList;
use barrelstrength\sproutlists\models\Settings;
use barrelstrength\sproutlists\services\App;
use barrelstrength\sproutlists\services\Lists;
use barrelstrength\sproutlists\web\twig\extensions\TwigExtensions;
use barrelstrength\sproutlists\web\twig\variables\SproutListsVariable;
use Craft;
use craft\base\Plugin;
use craft\elements\User;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\helpers\UrlHelper;
use craft\services\UserPermissions;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use craft\web\View;
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
    /**
     * Enable use of SproutLists::$app-> in place of Craft::$app->
     *
     * @var App
     */
    public static $app;

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
    public $schemaVersion = '4.0.6';

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

        self::$app = new App();

        SproutBaseHelper::registerModule();

        Craft::setAlias('@sproutlists', $this->getBasePath());

        Event::on(Lists::class, Lists::EVENT_REGISTER_LIST_TYPES, static function(RegisterListTypesEvent $event) {
            $event->listTypes[] = SubscriberList::class;
//            $event->listTypes[] = WishList::class;
        });

        // Setup Template Roots
        Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function(RegisterTemplateRootsEvent $e) {
            $e->roots['sprout-lists'] = $this->getBasePath().DIRECTORY_SEPARATOR.'templates';
        });

        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, static function(Event $event) {
            $event->sender->set('sproutLists', SproutListsVariable::class);
        });

        Event::on(User::class, User::EVENT_AFTER_SAVE, static function(Event $event) {
            if (Craft::$app->getPlugins()->isPluginEnabled('sprout-lists')) {
                SproutLists::$app->subscribers->handleUpdateUserIdOnSaveEvent($event);
            }
        });

        Event::on(User::class, User::EVENT_AFTER_DELETE, static function(Event $event) {
            if (Craft::$app->getPlugins()->isPluginEnabled('sprout-lists')) {
                SproutLists::$app->subscribers->handleUpdateUserIdOnDeleteEvent($event);
            }
        });

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, $this->getCpUrlRules());
        });

        Event::on(UserPermissions::class, UserPermissions::EVENT_REGISTER_PERMISSIONS, function(RegisterUserPermissionsEvent $event) {
            $event->permissions['Sprout Lists'] = $this->getUserPermissions();
        });

        Event::on(DataSources::class, DataSources::EVENT_REGISTER_DATA_SOURCES, static function(RegisterComponentTypesEvent $event) {
            $event->types[] = SubscriberListDataSource::class;
        });

        Craft::$app->view->registerTwigExtension(new TwigExtensions());
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

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    protected function afterInstall()
    {
        if (!Craft::$app->getPlugins()->isPluginInstalled('sprout-reports')) {
            return;
        }

        $dataSourceTypes = [
            SubscriberListDataSource::class
        ];

        foreach ($dataSourceTypes as $dataSourceClass) {
            /** @var DataSource $dataSource */
            $dataSource = new $dataSourceClass();
            $dataSource->viewContext = 'sprout-lists';
            SproutBaseReports::$app->dataSources->saveDataSource($dataSource);
        }
    }

    private function getCpUrlRules(): array
    {
        return [
            'sprout-lists' =>
                'sprout-lists/lists/lists-index-template',

            // Subscribers
            'sprout-lists/subscribers/new' =>
                'sprout-lists/subscribers/edit-subscriber-template',
            'sprout-lists/subscribers/edit/<id:\d+>' =>
                'sprout-lists/subscribers/edit-subscriber-template',
            'sprout-lists/subscribers/<listHandle:.*>' => [
                'template' => 'sprout-lists/subscribers'
            ],
            'sprout-lists/subscribers' =>
                'sprout-lists/subscribers/subscribers-index-template',

            // Lists
            'sprout-lists/lists' =>
                'sprout-lists/lists/lists-index-template',
            'sprout-lists/lists/new' =>
                'sprout-lists/lists/list-edit-template',
            'sprout-lists/lists/edit/<listId:\d+>' =>
                'sprout-lists/lists/list-edit-template',

            // Settings
            'sprout-lists/settings' =>
                'sprout/settings/edit-settings',
            'sprout-lists/settings/<settingsSectionHandle:.*>' =>
                'sprout/settings/edit-settings',
        ];
    }
}

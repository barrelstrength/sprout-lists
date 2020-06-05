<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutlists;

use barrelstrength\sproutbase\app\reports\base\DataSource;
use barrelstrength\sproutbase\app\reports\services\DataSources;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbase\SproutBaseHelper;
use barrelstrength\sproutlists\events\RegisterListTypesEvent;
use barrelstrength\sproutlists\integrations\sproutreports\datasources\SubscriberListDataSource;
use barrelstrength\sproutlists\listtypes\SubscriberList;
use barrelstrength\sproutlists\services\App;
use barrelstrength\sproutlists\services\Lists;
use barrelstrength\sproutlists\web\twig\extensions\TwigExtensions;
use barrelstrength\sproutlists\web\twig\variables\SproutListsVariable;
use Craft;
use craft\base\Plugin;
use craft\elements\User;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\web\twig\variables\CraftVariable;
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

        Event::on(DataSources::class, DataSources::EVENT_REGISTER_DATA_SOURCES, static function(RegisterComponentTypesEvent $event) {
            $event->types[] = SubscriberListDataSource::class;
        });

        Craft::$app->view->registerTwigExtension(new TwigExtensions());
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
            SproutBase::$app->dataSources->saveDataSource($dataSource);
        }
    }
}

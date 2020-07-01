<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutlists;

use barrelstrength\sproutbase\app\reports\base\DataSource;
use barrelstrength\sproutbase\app\reports\services\DataSources;
use barrelstrength\sproutbase\config\base\SproutBasePlugin;
use barrelstrength\sproutbase\config\configs\FormsConfig;
use barrelstrength\sproutbase\config\configs\ListsConfig;
use barrelstrength\sproutbase\config\configs\NotificationsConfig;
use barrelstrength\sproutbase\config\configs\ReportsConfig;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbase\SproutBaseHelper;
use barrelstrength\sproutbase\app\lists\events\RegisterListTypesEvent;
use barrelstrength\sproutbase\app\lists\integrations\sproutreports\datasources\SubscriberListDataSource;
use barrelstrength\sproutbase\app\lists\listtypes\SubscriberList;
use barrelstrength\sproutbase\app\lists\services\App;
use barrelstrength\sproutbase\app\lists\services\Lists;
use barrelstrength\sproutbase\app\lists\web\twig\extensions\TwigExtensions;
use barrelstrength\sproutbase\app\lists\web\twig\variables\ListsVariable;
use Craft;
use craft\base\Plugin;
use craft\elements\User;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\web\twig\variables\CraftVariable;
use craft\web\View;
use yii\base\Event;

class SproutLists extends SproutBasePlugin
{
    /**
     * @var string
     */
    public $schemaVersion = '4.0.6';

    /**
     * @var string
     */
    public $minVersionRequired = '0.7.1';

    public static function getSproutConfigs(): array
    {
        return [
            ListsConfig::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        SproutBaseHelper::registerModule();

        Event::on(Lists::class, Lists::EVENT_REGISTER_LIST_TYPES, static function(RegisterListTypesEvent $event) {
            $event->listTypes[] = SubscriberList::class;
//            $event->listTypes[] = WishList::class;
        });

        Event::on(User::class, User::EVENT_AFTER_SAVE, static function(Event $event) {
            // @todo - update to use module enabled check
            if (Craft::$app->getPlugins()->isPluginEnabled('sprout-lists')) {
                SproutBase::$app->subscribers->handleUpdateUserIdOnSaveEvent($event);
            }
        });

        Event::on(User::class, User::EVENT_AFTER_DELETE, static function(Event $event) {
            // @todo - update to use module enabled check
            if (Craft::$app->getPlugins()->isPluginEnabled('sprout-lists')) {
                SproutBase::$app->subscribers->handleUpdateUserIdOnDeleteEvent($event);
            }
        });
    }

    protected function afterInstall()
    {
        $dataSourceTypes = [
            SubscriberListDataSource::class
        ];

        SproutBase::$app->dataSources->installDataSources($dataSourceTypes);
    }
}

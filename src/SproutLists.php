<?php

namespace barrelstrength\sproutlists;

use barrelstrength\sproutbase\base\BaseSproutTrait;
use barrelstrength\sproutbase\SproutBaseHelper;
use barrelstrength\sproutlists\integrations\sproutlists\SubscriberListType;
use barrelstrength\sproutlists\models\Settings;
use barrelstrength\sproutlists\services\App;
use barrelstrength\sproutlists\services\Lists;
use barrelstrength\sproutlists\web\twig\extensions\TwigExtensions;
use barrelstrength\sproutlists\web\twig\variables\SproutListsVariable;
use craft\base\Plugin;
use Craft;
use craft\events\RegisterUrlRulesEvent;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use yii\base\Event;

/**
 * Class SproutListsPlugin
 *
 * @package Craft
 */
class SproutLists extends Plugin
{
    use BaseSproutTrait;

    /**
     * Enable use of SproutLists::$plugin-> in place of Craft::$app->
     *
     * @var \barrelstrength\sproutlists\services\App
     */
    public static $app;

    /**
     * @var string
     */
    public static $pluginId = 'sprout-lists';

    /**
     * @var bool
     */
    public $hasSettings = true;

    public function init()
    {
        parent::init();

        SproutBaseHelper::registerModule();

        $this->setComponents([
            'app' => App::class
        ]);

        $this->hasCpSection = true;

        self::$app = $this->get('app');

        Craft::setAlias('@sproutlists', $this->getBasePath());

        Craft::$app->view->twig->addExtension(new TwigExtensions());

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {

            $event->rules['sprout-lists/lists/new'] = 'sprout-lists/lists/edit-list-template';
            $event->rules['sprout-lists/lists/edit/<listId:\d+>'] = 'sprout-lists/lists/edit-list-template';
            $event->rules['sprout-lists/subscribers/new'] = 'sprout-lists/subscribers/edit-subscriber-template';
            $event->rules['sprout-lists/subscribers/edit/<id:\d+>'] = 'sprout-lists/subscribers/edit-subscriber-template';

            $event->rules['sprout-lists/settings'] = 'sprout-base/settings/edit-settings';
            $event->rules['sprout-lists/settings/<settingsSectionHandle:.*>'] = 'sprout-base/settings/edit-settings';

            $event->rules['sprout-lists/subscribers/<listHandle:.*>'] = [
                'template' => 'sprout-lists/subscribers'
            ];

            return $event;
        });

        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event) {
            $variable = $event->sender;

            $variable->set('sproutLists', SproutListsVariable::class);
        });

        // @todo - sort out enableUserSync
        if ($this->getSettings()->enableUserSync) {
//            craft()->on('users.saveUser', function(Event $event) {
//                sproutLists()->subscribers->updateUserIdOnSave($event);
//            });
//
//            craft()->on('users.onDeleteUser', function(Event $event) {
//                sproutLists()->subscribers->updateUserIdOnDelete($event);
//            });
        }

        Event::on(Lists::class, Lists::EVENT_REGISTER_LIST_TYPES, function(Event $event) {
            $event->listTypes[] = SubscriberListType::class;
        });
    }

    /**
     * @return Settings|\craft\base\Model|null
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Sprout Lists';
    }

    public function getCpNavItem()
    {
        $parent = parent::getCpNavItem();

        $navigation = [
            'subnav' => [
                'subscribers' => [
                    'label' => Craft::t('sprout-lists', 'Subscribers'),
                    'url' => 'sprout-lists/subscribers'
                ],
                'lists' => [
                    'label' => Craft::t('sprout-lists', 'Lists'),
                    'url' => 'sprout-lists/lists'
                ],
                'settings' => [
                    'label' => Craft::t('sprout-lists', 'Settings'),
                    'url' => 'sprout-lists/settings/general'
                ]
            ],
        ];

        return array_merge($parent, $navigation);
    }
}
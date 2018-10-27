<?php

namespace barrelstrength\sproutlists;

use barrelstrength\sproutbase\base\BaseSproutTrait;
use barrelstrength\sproutbase\SproutBaseHelper;
use barrelstrength\sproutlists\events\RegisterListTypesEvent;
use barrelstrength\sproutlists\listtypes\SubscriberListType;
use barrelstrength\sproutlists\models\Settings;
use barrelstrength\sproutlists\services\App;
use barrelstrength\sproutlists\services\Lists;
use barrelstrength\sproutlists\web\twig\extensions\TwigExtensions;
use barrelstrength\sproutlists\web\twig\variables\SproutListsVariable;

use craft\base\Plugin;
use Craft;
use craft\elements\User;
use craft\events\ElementEvent;

use craft\events\RegisterUrlRulesEvent;
use craft\helpers\UrlHelper;

use craft\services\Elements;
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
    public $schemaVersion = '4.0.1';

    /**
     * @var string
     */
    public $minVersionRequired = '0.7.1';

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        SproutBaseHelper::registerModule();

        $this->setComponents([
            'app' => App::class
        ]);

        self::$app = $this->get('app');

        Craft::setAlias('@sproutlists', $this->getBasePath());

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {

            $event->rules['sprout-lists'] = ['template' => 'sprout-base-lists/index'];
            $event->rules['sprout-lists/lists'] = ['template' => 'sprout-base-lists/lists/index'];
            $event->rules['sprout-lists/lists/new'] = 'sprout-lists/lists/edit-list-template';
            $event->rules['sprout-lists/lists/edit/<listId:\d+>'] = 'sprout-lists/lists/edit-list-template';

            $event->rules['sprout-lists/subscribers'] = ['template' => 'sprout-base-lists/subscribers'];
            $event->rules['sprout-lists/subscribers/new'] = 'sprout-lists/subscribers/edit-subscriber-template';
            $event->rules['sprout-lists/subscribers/edit/<id:\d+>'] = 'sprout-lists/subscribers/edit-subscriber-template';

            $event->rules['sprout-lists/settings'] = 'sprout/settings/edit-settings';
            $event->rules['sprout-lists/settings/<settingsSectionHandle:.*>'] = 'sprout/settings/edit-settings';

            $event->rules['sprout-lists/subscribers/<listHandle:.*>'] = [
                'template' => 'sprout-base-lists/subscribers'
            ];

            return $event;
        });

        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event) {
            $variable = $event->sender;

            $variable->set('sproutLists', SproutListsVariable::class);
        });

        Craft::$app->view->registerTwigExtension(new TwigExtensions());

        Event::on(Lists::class, Lists::EVENT_REGISTER_LIST_TYPES, function(RegisterListTypesEvent $event) {
            $event->listTypes[] = SubscriberListType::class;
        });

        if ($this->getSettings()->enableUserSync) {
            Event::on(Elements::class, Elements::EVENT_AFTER_SAVE_ELEMENT, function(ElementEvent $event) {
                if ($event->element instanceof User) {
                    SproutLists::$app->subscribers->updateUserIdOnSave($event);
                }
            });
            Event::on(Elements::class, Elements::EVENT_AFTER_DELETE_ELEMENT, function(ElementEvent $event) {
                if ($event->element instanceof User) {
                    SproutLists::$app->subscribers->updateUserIdOnDelete($event);
                }
            });
        }
    }

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * Redirect to Sprout Lists settings
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

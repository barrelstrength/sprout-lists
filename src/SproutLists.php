<?php

namespace barrelstrength\sproutlists;

use barrelstrength\dummy\services\App;
use barrelstrength\sproutbase\base\BaseSproutTrait;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbase\SproutBaseHelper;
use barrelstrength\sproutlists\models\Settings;
use craft\base\Plugin;
use Craft;
use craft\events\RegisterUrlRulesEvent;
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

    public $hasSettings = true;

    /**
     * Enable use of SproutEmail::$plugin-> in place of Craft::$app->
     *
     * @var \barrelstrength\sproutemail\services\App
     */
    public static $app;
    public static $pluginId = 'sprout-lists';

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

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {

            $event->rules['sprout-lists/lists/new']                = 'sprout-lists/lists/edit-list-template';
            $event->rules['sprout-lists/lists/edit/<emailId:\d+>'] = 'sprout-lists/lists/edit-list-template';
            $event->rules['sprout-lists/subscribers/new']          = 'sprout-lists/subscribers/edit-subscriber-template';
            $event->rules['sprout-lists/edit/<id:\d+>']            = 'sprout-lists/subscribers/edit-subscriber-template';

            $event->rules['sprout-lists/settings'] = 'sprout-base/settings/edit-settings';
            $event->rules['sprout-lists/settings/<settingsSectionHandle:.*>'] = 'sprout-base/settings/edit-settings';

            return $event;
        });

        if ($this->getSettings()->enableUserSync) {
//            craft()->on('users.saveUser', function(Event $event) {
//                sproutLists()->subscribers->updateUserIdOnSave($event);
//            });
//
//            craft()->on('users.onDeleteUser', function(Event $event) {
//                sproutLists()->subscribers->updateUserIdOnDelete($event);
//            });
        }
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
                    'label' => static::t('Subscribers'),
                    'url' => 'sprout-lists/subscribers'
                ],
                'lists' => [
                    'label' => static::t('Lists'),
                    'url' => 'sprout-lists/lists'
                ],
                'settings' => [
                    'label' => static::t('Settings'),
                    'url' => 'sprout-lists/settings/general'
                ]
            ],
        ];

        return array_merge($parent, $navigation);
    }
}
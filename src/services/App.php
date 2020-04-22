<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutlists\services;

use barrelstrength\sproutlists\models\Settings;
use barrelstrength\sproutlists\SproutLists;
use craft\base\Component;

/**
 * App Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Barrelstrength
 * @package   SproutLists
 * @since     3
 *
 * @property Settings $settings
 */
class App extends Component
{
    /**
     * @var $lists Lists
     */
    public $lists;

    /**
     * @var $subscribers Subscribers
     */
    public $subscribers;

    public function init()
    {
        parent::init();

        $this->lists = new Lists();
        $this->subscribers = new Subscribers();
    }

    /**
     * Returns plugin settings model.
     *
     * This method helps explicitly define what we're getting back so we can
     * avoid NullReferenceException warnings
     *
     * @return Settings
     */
    public function getSettings(): Settings
    {
        /** @var SproutLists $plugin */
        $plugin = SproutLists::getInstance();

        /** @var Settings $settings */
        $settings = $plugin->getSettings();

        return $settings;
    }
}

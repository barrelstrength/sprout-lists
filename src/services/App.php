<?php

namespace barrelstrength\sproutlists\services;

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
}

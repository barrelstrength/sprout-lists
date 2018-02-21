<?php

namespace barrelstrength\sproutlists\services;

use barrelstrength\sproutbase\base\TemplateTrait;
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
    use TemplateTrait;

    const ERROR = 'sproutListsError';

    /**
     * @var $lists Lists
     */
    public $lists;
    public $subscriptions;

    public $subscribers;

    public function init()
    {
        parent::init();

        $this->lists       = new Lists();
        $this->subscribers = new Subscribers();
    }
}

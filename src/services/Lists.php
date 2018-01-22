<?php

namespace barrelstrength\sproutlists\services;

use barrelstrength\sproutlists\events\RegisterListTypesEvent;
use craft\base\Component;

class Lists extends Component
{
    public const EVENT_REGISTER_LIST_TYPES = 'registerSproutListsListTypes';
    /**
     * Registered List Types
     *
     * @var array
     */
    protected $listTypes = [];

    /**
     * Initializes the application component.
     *
     * This method will load all available List Types
     *
     * @return null
     */
    public function init()
    {
        parent::init();

        $this->getAllListTypes();

        return null;
    }

    /**
     * Gets all registered list types.
     *
     * @return array
     */
    public function getAllListTypes()
    {
        $event = new RegisterListTypesEvent([
            'listTypes' => []
        ]);

        $this->trigger(self::EVENT_REGISTER_LIST_TYPES, $event);

        $listTypes = $event->listTypes;

        if (!empty($listTypes)) {
            foreach ($listTypes as $listType) {
                $namespace = get_class($listType);
                $this->listTypes[$namespace] = $listType;
            }
        }

        return $this->listTypes;
    }

    /**
     * Returns a new List Type Class for the given List Type
     * @param $className
     *
     * @return mixed
     * @throws \Exception
     */
    public function getListType($className)
    {
        if (!isset($this->listTypes[$className])) {
            throw new \Exception('Invalid List Type.');
        }

        return new $this->listTypes[$className];
    }
}
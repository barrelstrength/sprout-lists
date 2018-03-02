<?php

namespace barrelstrength\sproutlists\services;

use barrelstrength\sproutbase\contracts\sproutlists\SproutListsBaseListType;
use barrelstrength\sproutlists\events\RegisterListTypesEvent;
use barrelstrength\sproutlists\records\Subscription;
use craft\base\Component;
use barrelstrength\sproutlists\records\Lists as ListsRecord;

class Lists extends Component
{
    const EVENT_REGISTER_LIST_TYPES = 'registerSproutListsListTypes';
    /**
     * Registered List Types
     *
     * @var array
     */
    protected $listTypes = [];

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
                /**
                 * @var $listType SproutListsBaseListType
                 */
                $this->listTypes[$listType->getHandle()] = $listType;
            }
        }

        return $this->listTypes;
    }

    /**
     * Returns a new List Type Class for the given List Type
     *
     * @param $className
     *
     * @return mixed
     * @throws \Exception
     */
    public function getListType($className)
    {
        $listTypes = $this->getAllListTypes();

        if (!isset($listTypes[$className])) {
            throw new \Exception('Invalid List Type.');
        }

        return new $listTypes[$className];
    }

    /**
     * Deletes a list.
     *
     * @param $listId
     *
     * @return bool
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function deleteList($listId)
    {
        $listRecord = ListsRecord::findOne($listId);

        if ($listRecord == null) {
            return false;
        }

        if ($listRecord AND $listRecord->delete()) {
            $subscriptions = Subscription::find()->where([
                'listId' => $listId
            ]);

            if ($subscriptions != null) {
                Subscription::deleteAll('listId = :listId', [
                    ':listId' => $listId
                ]);
            }

            return true;
        }

        return false;
    }
}
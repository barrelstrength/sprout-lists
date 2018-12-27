<?php

namespace barrelstrength\sproutlists\services;

use barrelstrength\sproutbase\app\lists\base\ListType;
use barrelstrength\sproutlists\events\RegisterListTypesEvent;
use barrelstrength\sproutlists\listtypes\SubscriberListType;
use barrelstrength\sproutlists\records\Subscription;
use craft\base\Component;
use barrelstrength\sproutlists\records\SubscriberList as ListsRecord;
use yii\base\Exception;


/**
 *
 * @property array $allListTypes
 */
class Lists extends Component
{
    /**
     * @event RegisterListTypesEvent
     */
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
    public function getAllListTypes(): array
    {
        $event = new RegisterListTypesEvent([
            'listTypes' => []
        ]);

        $this->trigger(self::EVENT_REGISTER_LIST_TYPES, $event);

        $listTypes = $event->listTypes;

        if (!empty($listTypes)) {
            foreach ($listTypes as $listTypeClass) {
                /**
                 * @var ListType $listType
                 */
                $listType = new $listTypeClass();

                $this->listTypes[$listTypeClass] = $listType;
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
     * @throws Exception
     */
    public function getListType($className)
    {
        $listTypes = $this->getAllListTypes();

        if (!isset($listTypes[$className])) {
            throw new Exception('Invalid List Type.');
        }

        return new $listTypes[$className];
    }

    /**
     * @param $listHandle
     *
     * @return SubscriberListType
     */
    public function getListTypeByHandle($listHandle): SubscriberListType
    {
        /** @var ListsRecord $list */
        $list = ListsRecord::find()->where([
            'handle' => $listHandle
        ])->one();

        if ($list === null) {
            return new SubscriberListType();
        }

        return new $list->type;
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
    public function deleteList($listId): bool
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
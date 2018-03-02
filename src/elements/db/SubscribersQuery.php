<?php

namespace barrelstrength\sproutlists\elements\db;

use craft\elements\db\ElementQuery;

class SubscribersQuery extends ElementQuery
{
    public $listId;

    /**
     * @inheritdoc
     */
    protected function beforePrepare(): bool
    {
        $this->joinElementTable('sproutlists_subscribers');
        $this->query->select([
            'sproutlists_subscribers.userId',
            'sproutlists_subscribers.email',
            'sproutlists_subscribers.firstName',
            'sproutlists_subscribers.lastName',
            'sproutlists_subscribers.dateCreated',
            'sproutlists_subscribers.dateUpdated'
        ]);

        if ($this->listId != null) {
            $this->query->leftJoin('sproutlists_subscriptions subscriptions', 'subscriptions.subscriberId = sproutlists_subscribers.id');
            $this->query->leftJoin('sproutlists_lists lists', 'lists.id = subscriptions.listId');
            $this->query->where('subscriptions.listId = '.$this->listId);
        }
        return parent::beforePrepare();
    }
}
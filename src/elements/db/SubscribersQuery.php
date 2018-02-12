<?php

namespace barrelstrength\sproutlists\elements\db;

use craft\elements\db\ElementQuery;

class SubscribersQuery extends ElementQuery
{
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

        return parent::beforePrepare();
    }
}
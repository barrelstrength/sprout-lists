<?php

namespace barrelstrength\sproutlists\elements\db;

use barrelstrength\sproutlists\records\Subscription as SubscriptionRecord;
use craft\elements\db\ElementQuery;

class SubscribersQuery extends ElementQuery
{
    /**
     * @var int
     */
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

        // @todo - can we optimize this query? This feels really inefficient
        if ($this->listId) {
            // Get all subscriptions for this list
            $subscriptions = SubscriptionRecord::find()
                ->select('subscriberId')
                ->where([
                    'listId' => $this->listId
                ])->all();

            // Filter so we only have ids
            $subscriberIds = array_map(function($subscriptions) {
                return $subscriptions->subscriberId;
            }, $subscriptions);

            // Only return subscribers that match this query
            $this->subQuery->andWhere(['in',
                'sproutlists_subscribers.id',
                array_unique($subscriberIds, SORT_REGULAR)
            ]);
        }

        return parent::beforePrepare();
    }
}

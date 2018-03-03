<?php

namespace barrelstrength\sproutlists\web\twig\extensions;

use Craft;
use craft\helpers\StringHelper;
use \Twig_Extension;
use barrelstrength\sproutactive\SproutActive;

class TwigExtensions extends Twig_Extension
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'Sprout Lists';
    }

    /**
     * Makes the filters available to the template context
     *
     * @return array|\Twig_Filter[]
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('subscriberUserIds', [$this, 'subscriberUserIds'])
        ];
    }

    /**
     * Create a comma, separated list of Subscriber Element ids
     *
     * @param $subscriptions
     *
     * @return mixed
     */
    public function subscriberUserIds($subscriptions)
    {
        $subscriptionIds = $this->buildArrayOfIds($subscriptions, 'userId');

        $subscriptionIds = array_keys(array_count_values($subscriptionIds));

        return StringHelper::toString($subscriptionIds);
    }

    /**
     * Build an array of ids from our Subscriptions
     *
     * @param $subscriptions
     * @param $attribute
     *
     * @return array
     */
    public function buildArrayOfIds($subscriptions, $attribute)
    {
        $ids = [];

        foreach ($subscriptions as $subscription) {
            $ids[] = $subscription[$attribute];
        }

        return $ids;
    }
}

<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutlists\web\twig\extensions;


use craft\helpers\StringHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TwigExtensions extends AbstractExtension
{
    /**
     * Makes the filters available to the template context
     *
     * @return array|TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('subscriberUserIds', [$this, 'subscriberUserIds'])
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
    public function buildArrayOfIds($subscriptions, $attribute): array
    {
        $ids = [];

        foreach ($subscriptions as $subscription) {
            if ($subscription[$attribute] !== null) {
                $ids[] = $subscription[$attribute];
            }
        }

        return $ids;
    }
}

<?php

namespace Craft;

class SproutListsTwigExtension extends \Twig_Extension
{
	/**
	 * Create our Twig Functions
	 *
	 * @return array
	 */
	public function getFilters()
	{
		return array(
			'subscriberUserIds'   => new \Twig_Filter_Method($this, 'subscriberUserIds')
		);
	}

	/**
	 * Create a comma, separated list of Subscriber Element ids
	 *
	 * @return string
	 */
	public function subscriberUserIds($subscriptions)
	{
		$subscriptionIds = $this->buildArrayOfIds($subscriptions, 'userId');

		$subscriptionIds = array_values(array_unique($subscriptionIds));

		return StringHelper::arrayToString($subscriptionIds);
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
		$ids = array();

		foreach ($subscriptions as $subscription)
		{
			$ids[] = $subscription[$attribute];
		}

		return $ids;
	}
}

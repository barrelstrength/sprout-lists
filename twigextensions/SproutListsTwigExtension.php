<?php
namespace Craft;

class SproutListsTwigExtension extends \Twig_Extension
{
  /**
   * Plugin Name
   * 
   * @return string
   */
  public function getName()
  {
    return 'Sprout List';
  }

  /**
   * Create our Twig Functions
   * 
   * @return array
   */
  public function getFilters()
  {
    return array(
      'subscriptionIds' => new \Twig_Filter_Method($this, 'subscriptionIds'),
      'subscriberIds' => new \Twig_Filter_Method($this, 'subscriberIds'),
    );
  }

	/**
	 * Create a comma, separated list of element ids
	 *
	 * @return string
	 */
  public function subscriptionIds($subscriptions)
  {
	  $subscriptionIds = $this->buildArrayOfIds($subscriptions, 'elementId');
	  return StringHelper::arrayToString($subscriptionIds);
  }

	/**
	 * Create a comma, separated list of user ids
	 *
	 * @return string
	 */
	public function subscriberIds($subscriptions)
	{
		$subscriptionIds = $this->buildArrayOfIds($subscriptions, 'userId');
		return StringHelper::arrayToString($subscriptionIds);
	}

	/**
	 * @param $subscriptions
	 * @return array
	 */
	public function buildArrayOfIds($subscriptions, $type)
	{
		$subscriptionIds = array();

		foreach ($subscriptions as $subscription)
		{
			$subscriptionIds[] = $subscription[$type];
		}

		return $subscriptionIds;
	}
}

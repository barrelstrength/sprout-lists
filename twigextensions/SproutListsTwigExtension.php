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
    return 'Sprout Lists';
  }

  /**
   * Create our Twig Functions
   * 
   * @return array
   */
  public function getFilters()
  {
    return array(
      'subscriberIds'   => new \Twig_Filter_Method($this, 'subscriberIds'),
      'subscriptionIds' => new \Twig_Filter_Method($this, 'subscriptionIds'),
    );
  }

	/**
	 * Create a comma, separated list of element ids
	 *
	 * @return string
	 */
  public function listIds($lists)
  {
	  $listIds = $this->buildArrayOfIds($lists, 'elementId');
	  return StringHelper::arrayToString($listIds);
  }

	/**
	 * Create a comma, separated list of user ids
	 *
	 * @return string
	 */
	public function subscriberIds($subscriptions)
	{
		$subscriptionIds = $this->buildArrayOfIds($subscriptions, 'userId');

		$subscriptionIds = array_values(array_unique($subscriptionIds));

		return StringHelper::arrayToString($subscriptionIds);
	}

	public function subscriptionIds($subscriptions)
	{
		$subscriptionIds = $this->buildArrayOfIds($subscriptions, 'listId');
		$subscriptionIds = array_values(array_unique($subscriptionIds));

		return StringHelper::arrayToString($subscriptionIds);
	}

	/**
	 * @param $lists
	 * @return array
	 */
	public function buildArrayOfIds($lists, $type)
	{
		$listIds = array();

		foreach ($lists as $list)
		{
			$listIds[] = $list[$type];
		}

		return $listIds;
	}
}

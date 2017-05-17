<?php

namespace Craft;

/**
 * Class SproutListsService
 *
 * @package Craft
 * --
 * @property SproutLists_ListsService         $lists
 * @property SproutLists_SubscribersService   $subscribers
 */
class SproutListsService extends BaseApplicationComponent
{
	public $lists;
	public $subscriptions;
	public $subscribers;

	public function init()
	{
		parent::init();

		$this->lists       = Craft::app()->getComponent('sproutLists_lists');
		$this->subscribers = Craft::app()->getComponent('sproutLists_subscribers');
	}

	public function getListTypes()
	{
		$typesToLoad = craft()->plugins->call('registerSproutListsListTypes');

		$classes = array();

		if ($typesToLoad)
		{
			foreach ($typesToLoad as $plugin => $types)
			{
				foreach ($types as $type)
				{
					if ($type && $type instanceof SproutListsBaseListType)
					{
						$classes[$type->getClassName()] = $type;
						continue;
					}
				}
			}
		}

		ksort($classes);

		return $classes;
	}
}

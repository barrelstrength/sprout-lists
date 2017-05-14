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
}

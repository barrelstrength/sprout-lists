<?php
namespace Craft;

/**
 * Class SproutListsService
 *
 * @package Craft
 * --
 * @property SproutLists_ListsService         $lists
 * @property SproutLists_SubscriptionsService $subscriptions
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

		$this->lists         = Craft::app()->getComponent('sproutLists_lists');
		$this->subscriptions = Craft::app()->getComponent('sproutLists_subscriptions');
		$this->subscribers   = Craft::app()->getComponent('sproutLists_subscribers');
	}
}

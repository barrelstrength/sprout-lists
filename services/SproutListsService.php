<?php

namespace Craft;

class SproutListsService extends BaseApplicationComponent
{
	/**
	 * @property SproutLists_ListsService $lists
	 */
	public $lists;

	/**
	 * @property SproutLists_SubscriptionsService $subscriptions
	 */
	public $subscriptions;

	/**
	 * @property SproutLists_SubscribersService $subscribers
	 */
	public $subscribers;

	public function init()
	{
		parent::init();

		$this->lists         = Craft::app()->getComponent('sproutLists_lists');
		$this->subscriptions = Craft::app()->getComponent('sproutLists_subscriptions');
		$this->subscribers   = Craft::app()->getComponent('sproutLists_subscribers');
	}
}

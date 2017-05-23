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
	const ERROR = 'sproutListsError';

	public $lists;
	public $subscriptions;
	public $subscribers;

	public function init()
	{
		parent::init();

		$this->lists       = Craft::app()->getComponent('sproutLists_lists');
		$this->subscribers = Craft::app()->getComponent('sproutLists_subscribers');
	}

	/**
	 * @param $message
	 */
	public function addError($message)
	{
		craft()->httpSession->add(static::ERROR, $message);
	}

	/**
	 * @return mixed
	 */
	public function getError()
	{
		$message = craft()->httpSession->get(static::ERROR);

		// Delete after to make it a flash message.
		if (craft()->httpSession->contains((static::ERROR)))
		{
			craft()->httpSession->remove(static::ERROR);
		}

		return $message;
	}
}

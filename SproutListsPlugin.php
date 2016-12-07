<?php
namespace Craft;

class SproutListsPlugin extends BasePlugin
{
	public function getName()
	{
		return 'Sprout Lists';
	}

	public function getVersion()
	{
		return '0.6.1';
	}

	public function getDeveloper()
	{
		return 'Barrel Strength Design';
	}

	public function getDeveloperUrl()
	{
		return 'http://barrelstrengthdesign.com';
	}

	/**
	 * @return bool
	 */
	public function hasCpSection()
	{
		return true;
	}

	public function init()
	{
		parent::init();

		if (craft()->request->isCpRequest())
		{
			craft()->templates->includeJsResource('sproutlists/js/SproutListsIndex.js');
		}
	}

	public function registerCpRoutes()
	{
		return array(
			'sproutlists/lists/new' => array(
				'action' => 'sproutLists/lists/editList'
			),
			'sproutlists/lists/edit/(?P<listId>[\d]+)' => array(
				'action' => 'sproutLists/lists/editList'
			),
			'sproutlists/subscribers/new' => array(
				'action' => 'sproutLists/subscribers/editSubscriber'
			),
			'sproutlists/subscribers/(?P<listHandle>{handle})' =>
				'sproutlists/subscribers',

			'sproutlists/subscribers/edit/(?P<id>[\d]+)' => array(
				'action' => 'sproutLists/subscribers/editSubscriber'
			),
		);
	}

	public function registerSproutListsListType()
	{
		Craft::import('plugins.sproutlists.contracts.SproutListsBaseListType');
		Craft::import('plugins.sproutlists.integrations.sproutlists.SproutLists_UserListType');
		Craft::import('plugins.sproutlists.integrations.sproutlists.SproutLists_SubscriberListType');

		return array(
			new SproutLists_SubscriberListType()
		);
	}

	public function addTwigExtension()
	{
		Craft::import('plugins.sproutlists.twigextensions.SproutListsTwigExtension');

		return new SproutListsTwigExtension();
	}
}

/**
 * @return SproutListsService
 */
function sproutLists()
{
	return Craft::app()->getComponent('sproutLists');
}

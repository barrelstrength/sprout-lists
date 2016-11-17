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

	public function registerCpRoutes()
	{
		return array(
			'sproutlists/lists/new' => array(
				'action' => 'sproutLists/lists/editList'
			),
			'sproutlists/lists/edit/(?P<listId>[\d]+)' => array(
				'action' => 'sproutLists/lists/editList'
			),
			'sproutlists/users/new' => array(
				'action' => 'sproutLists/users/editUserRecipient'
			),
			'sproutlists/users/edit/(?P<id>[\d]+)' => array(
				'action' => 'sproutLists/users/editUserRecipient'
			),
			'sproutlists/emails/new' => array(
				'action' => 'sproutLists/emails/editEmailRecipient'
			),
			'sproutlists/emails/edit/(?P<id>[\d]+)' => array(
				'action' => 'sproutLists/emails/editEmailRecipient'
			),
		);
	}

	public function registerSproutListsListType()
	{
		Craft::import('plugins.sproutlists.contracts.SproutListsBaseListType');
		Craft::import('plugins.sproutlists.integrations.sproutlists.SproutLists_UserListType');
		Craft::import('plugins.sproutlists.integrations.sproutlists.SproutLists_EmailListType');

		return array(
			//new SproutLists_UserListType(),
			new SproutLists_EmailListType()
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

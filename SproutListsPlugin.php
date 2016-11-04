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

	public function registerSproutListsListType()
	{
		Craft::import('plugins.sproutlists.contracts.SproutListsBaseListType');
		Craft::import('plugins.sproutlists.integrations.sproutlists.SproutLists_UserListType');

		return array(
			new SproutLists_UserListType()
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

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

	public function addTwigExtension()
	{
		Craft::import('plugins.sproutlists.twigextensions.SproutListsTwigExtension');

		return new SproutListsTwigExtension();
	}
}

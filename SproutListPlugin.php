<?php
namespace Craft;

class SproutListPlugin extends BasePlugin
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
		Craft::import('plugins.sproutlist.twigextensions.SproutListTwigExtension');

		return new SproutListTwigExtension();
	}
}

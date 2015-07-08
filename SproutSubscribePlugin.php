<?php
namespace Craft;

class SproutSubscribePlugin extends BasePlugin
{
	public function getName()
	{
		return 'Sprout Subscribe';
	}

	public function getVersion()
	{
		return '0.6.0';
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
		Craft::import('plugins.sproutsubscribe.twigextensions.SproutSubscribeTwigExtension');

		return new SproutSubscribeTwigExtension();
	}
}

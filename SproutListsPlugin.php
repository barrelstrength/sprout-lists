<?php
namespace Craft;

/**
 * Class SproutListsPlugin
 *
 * @package Craft
 */
class SproutListsPlugin extends BasePlugin
{
	/**
	 * @return string
	 */
	public function getName()
	{
		return 'Sprout Lists';
	}

	/**
	 * @return string
	 */
	public function getVersion()
	{
		return '0.7.0';
	}

	/**
	 * @return string
	 */
	public function getSchemaVersion()
	{
		return '0.7.0';
	}

	/**
	 * @return string
	 */
	public function getDeveloper()
	{
		return 'Barrel Strength Design';
	}

	/**
	 * @return string
	 */
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

	protected function defineSettings()
	{
		return array(
			'enableUserSync'   => array(AttributeType::Bool, 'default' => false),
			'listTypeSettings' => array(AttributeType::Mixed, 'default' => false)
		);
	}

	public function getSettingsHtml()
	{
		return craft()->templates->render('sproutlists/_cp/settings', array(
			'settings' => $this->getSettings()
		));
	}

	/**
	 * @return array
	 */
	public function registerCpRoutes()
	{
		return array(
			'sproutlists/lists/new' => array(
				'action' => 'sproutLists/lists/editListTemplate'
			),
			'sproutlists/lists/edit/(?P<listId>[\d]+)' => array(
				'action' => 'sproutLists/lists/editListTemplate'
			),
			'sproutlists/subscribers/new' => array(
				'action' => 'sproutLists/subscribers/editSubscriberTemplate'
			),
			'sproutlists/subscribers/(?P<listHandle>{handle})' =>
				'sproutlists/subscribers',

			'sproutlists/subscribers/edit/(?P<id>[\d]+)' => array(
				'action' => 'sproutLists/subscribers/editSubscriberTemplate'
			),
		);
	}

	public function init()
	{
		parent::init();

		Craft::import('plugins.sproutlists.contracts.SproutListsBaseListType');
		Craft::import('plugins.sproutlists.integrations.sproutlists.SproutLists_SubscriberListType');

		if ($this->getSettings()->enableUserSync)
		{
			craft()->on('users.saveUser', function (Event $event) {
				sproutLists()->subscribers->updateUserIdOnSave($event);
			});

			craft()->on('users.onDeleteUser', function (Event $event) {
				sproutLists()->subscribers->updateUserIdOnDelete($event);
			});
		}
	}

	/**
	 * Register Twig Extensions
	 *
	 * @return SproutListsTwigExtension
	 */
	public function addTwigExtension()
	{
		Craft::import('plugins.sproutlists.twigextensions.SproutListsTwigExtension');

		return new SproutListsTwigExtension();
	}

	/**
	 * Register our default Sprout Lists List Types
	 *
	 * @return array
	 */
	public function registerSproutListsListTypes()
	{
		return array(
			new SproutLists_SubscriberListType()
		);
	}
}

/**
 * @return SproutListsService
 */
function sproutLists()
{
	return Craft::app()->getComponent('sproutLists');
}
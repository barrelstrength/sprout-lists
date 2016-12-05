<?php
namespace Craft;

class SproutListsVariable
{
	public function getName()
	{
		$plugin = craft()->plugins->getPlugin('sproutlists');

		return $plugin->getName();
	}

	public function getVersion()
	{
		$plugin = craft()->plugins->getPlugin('sproutlists');

		return $plugin->getVersion();
	}

	public function getIsSubscribed($criteria)
	{
		if (!isset($criteria['list']) OR !isset($criteria['elementId']))
		{
			throw new Exception(Craft::t('Missing arguments. list, userId, and elementId are all required.'));
		}

		$type = 'recipient';

		if (isset($criteria['type']))
		{
			$type = $criteria['type'];
		}

		$listType = sproutLists()->getListType($type);

		return $listType->isSubscribed($criteria);
	}

	// Counts
	// =========================================================================

	public function getSubscriberCount($criteria)
	{
		if (!isset($criteria['list']))
		{
			throw new Exception(Craft::t("Missing arguments. 'list' is required."));
		}

		$type = 'recipient';

		if (isset($criteria['type']))
		{
			$type = $criteria['type'];
		}

		$listType = sproutLists()->getListType($type);

		return $listType->getSubscriberCount($criteria);
	}

	public function getListCount($criteria)
	{
		if (!isset($criteria['list']))
		{
			throw new Exception(Craft::t("Missing arguments. 'list' is required."));
		}

		$type = 'recipient';

		if (isset($criteria['type']))
		{
			$type = $criteria['type'];
		}

		$listType = sproutLists()->getListType($type);

		return $listType->getListCount($criteria);
	}

	// Subscriptions
	// =========================================================================

	public function getSubscriptions($criteria = array())
	{
		$type = 'recipient';

		if (isset($criteria['type']))
		{
			$type = $criteria['type'];
		}

		$listType = sproutLists()->getListType($type);

		return $listType->getSubscriptions($criteria);
	}

	public function getSubscribers($criteria)
	{
		if (!isset($criteria['list']))
		{
			throw new Exception(Craft::t("Missing arguments. 'list' is required."));
		}

		$type = 'recipient';

		if (isset($criteria['type']))
		{
			$type = $criteria['type'];
		}

		$listType = sproutLists()->getListType($type);

		return $listType->getSubscribers($criteria);
	}

	public function getLists()
	{
		return sproutLists()->getLists();
	}

	public function getAllListTypes()
	{
		return sproutLists()->getAllListTypes();
	}

	public function getListsHtml($elementId = null)
	{
		return sproutLists()->getListsHtml($elementId);
	}

	public function getListsNav()
	{
		$navs = array();

		$lists = sproutLists()->getAllListTypes();

		if (!empty($lists))
		{
			foreach ($lists as $list)
			{
				$url = $list->getUrl();
				$navs[$url] = array(
					'label' => $list->getName(),
					'url'   => 'sproutlists/' . $url
				);
			}
		}

		return $navs;
	}

	public function getListElements()
	{
		return sproutLists()->getListElements();
	}

	public function getElementTitle($elementId)
	{
		return sproutLists()->getElementTitle($elementId);
	}
}
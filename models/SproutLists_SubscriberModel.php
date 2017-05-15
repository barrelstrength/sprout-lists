<?php

namespace Craft;

/**
 * Class SproutLists_SubscriberModel
 *
 * @package Craft
 * --
 * @property int $id
 * @property string $email
 * @property int $userId
 * @property string $firstName
 * @property string $lastName
 * @property mixed $subscriberLists
 */
class SproutLists_SubscriberModel extends BaseElementModel
{
	/**
	 * @var string
	 */
	protected $elementType = 'SproutLists_Subscriber';

	/**
	 * @var array
	 */
	protected $subscriberListsIds = array();

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->email;
	}

	/**
	 * @return array
	 */
	public function defineAttributes()
	{
		$defaults = parent::defineAttributes();

		$attributes = array(
			'id'              => array(AttributeType::Number),
			'email'           => array(AttributeType::Email, 'required' => true),
			'userId'          => array(AttributeType::Number),
			'firstName'       => array(AttributeType::String),
			'lastName'        => array(AttributeType::String),
			'subscriberLists' => array(AttributeType::Mixed),
			'dateCreated'     => array(AttributeType::DateTime)
		);

		return array_merge($defaults, $attributes);
	}

	/**
	 * @return false|string
	 */
	public function getCpEditUrl()
	{
		return UrlHelper::getCpUrl('sproutlists/subscribers/edit/' . $this->id);
	}

	/**
	 * Gets list IDs of all the lists to which this subscriber is subscribed.
	 *
	 * @return array
	 */
	public function getListIds()
	{
		if (empty($this->subscriberListsIds))
		{
			$subscriberLists = $this->getListsBySubscriberId();

			if (count($subscriberLists))
			{
				foreach ($subscriberLists as $list)
				{
					$this->subscriberListsIds[] = $list->id;
				}
			}
		}

		return $this->subscriberListsIds;
	}

	/**
	 * Gets an array of SproutLists_ListModels to which this subscriber is subscribed.
	 *
	 * @return array
	 */
	public function getListsBySubscriberId()
	{
		$lists    = array();
		$listType = sproutLists()->lists->getListType('subscriber');

		$subscriptionsRecord = SproutLists_SubscriptionRecord::model();

		$subscriptions = $subscriptionsRecord->findAllByAttributes(array(
			'subscriberId' => $this->id
		));

		if (count($subscriptions))
		{
			foreach ($subscriptions as $subscription)
			{
				$lists[] = $listType->getListById($subscription->listId);
			}
		}

		return $lists;
	}
}

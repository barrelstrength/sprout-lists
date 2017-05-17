<?php

namespace Craft;

/**
 * Class SproutLists_SubscriberRecord
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
class SproutLists_SubscriberRecord extends BaseRecord
{
	/**
	 * @return string
	 */
	public function getTableName()
	{
		return 'sproutlists_subscribers';
	}

	/**
	 * @return array
	 */
	public function defineAttributes()
	{
		return array(
			'userId'      => array(AttributeType::Number),
			'email'       => array(AttributeType::Email, 'required' => true, 'unique' => true),
			'firstName'   => array(AttributeType::String),
			'lastName'    => array(AttributeType::String)
		);
	}

	/**
	 * @return array
	 */
	public function defineRelations()
	{
		return array(
			'element'         => array(
				static::BELONGS_TO,
				'ElementRecord',
				'id',
				'required' => true,
				'onDelete' => static::CASCADE
			),
			'subscriberLists' => array(
				static::MANY_MANY,
				'SproutLists_ListRecord',
				'sproutlists_subscriptions(subscriberId, listId)'
			)
		);
	}
}

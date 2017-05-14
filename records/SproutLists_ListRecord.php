<?php

namespace Craft;

class SproutLists_ListRecord extends BaseRecord
{
	/**
	 * @return string
	 */
	public function getTableName()
	{
		return 'sproutlists_lists';
	}

	/**
	 * @return array
	 */
	public function defineAttributes()
	{
		return array(
			'elementId'        => array(AttributeType::Number),
			'type'             => array(AttributeType::String),
			'name'             => array(AttributeType::String, 'required' => true),
			'handle'           => array(AttributeType::String, 'required' => true),
			'totalSubscribers' => array(AttributeType::Number)
		);
	}

	/**
	 * @return array
	 */
	public function defineRelations()
	{
		return array(
			'element'     => array(
				static::BELONGS_TO,
				'ElementRecord',
				'id',
				'required' => true,
				'onDelete' => static::CASCADE
			),
			'subscribers' => array(
				static::MANY_MANY,
				'SproutLists_SubscriberRecord',
				'sproutlists_subscriptions(listId, subscriberId)'
			)
		);
	}
}

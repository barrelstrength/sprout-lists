<?php

namespace Craft;

class SproutLists_SubscriptionRecord extends BaseRecord
{
	/**
	 * @return string
	 */
	public function getTableName()
	{
		return 'sproutlists_subscriptions';
	}

	/**
	 * @return array
	 */
	public function defineAttributes()
	{
		return array(
			'subscriberId' => array(AttributeType::Number),
			'type'         => array(AttributeType::String)
		);
	}

	/**
	 * @return array
	 */
	public function defineRelations()
	{
		return array(
			'list' => array(
				static::BELONGS_TO,
				'SproutLists_ListRecord',
				'listId',
				'required' => true,
				'onDelete' => static::CASCADE
			)
		);
	}

	/**
	 * @return array
	 */
	public function defineIndexes()
	{
		return array(
			array('columns' => array('listId'))
		);
	}
}

<?php
namespace Craft;

class SproutLists_SubscriptionsRecord extends BaseRecord
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
			'subscriberId' => AttributeType::Number,
			'type' => AttributeType::String
		);
	}

	public function defineRelations()
	{
		return array(
			'list'    => array(
				static::BELONGS_TO,
				'SproutLists_ListRecord',
				'listId',
				'required' => true,
				'onDelete' => static::CASCADE
			)
		);
	}

	public function defineIndexes()
	{
		return array(
			array('columns' => array('listId'))
		);
	}
}

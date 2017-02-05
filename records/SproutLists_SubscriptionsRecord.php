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
			'listId'       => AttributeType::Number,
			'subscriberId' => AttributeType::Number,

			'type' => AttributeType::String,
			'elementId' => AttributeType::Number,
		);
	}

	public function defineRelations()
	{
		return array(
			'element' => array(
				static::BELONGS_TO,
				'ElementRecord',
				'elementId',
				'required' => true,
				'onDelete' => static::CASCADE
			),
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
			array('columns' => array('elementId'))
		);
	}
}

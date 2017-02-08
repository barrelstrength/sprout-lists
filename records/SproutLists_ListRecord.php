<?php
namespace Craft;

class SproutLists_ListRecord extends BaseRecord
{
	/**
	 * Return table name corresponding to this record
	 *
	 * @return string
	 */
	public function getTableName()
	{
		return 'sproutlists_lists';
	}

	/**
	 * These have to be explicitly defined in order for the plugin to install
	 *
	 * @todo - we have two elementIds here. One is the List Element ID
	 *       and the other is a relation to the Element ID to be associated with this List
	 *       can we enforce this in our db architecture with a fk?
	 *
	 * @return array
	 */
	public function defineAttributes()
	{
		return array(
			'elementId' => AttributeType::Number,
			'type'      => AttributeType::String,
			'name'      => AttributeType::String,
			'handle'    => AttributeType::String,
			'total'     => AttributeType::Number
		);
	}

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

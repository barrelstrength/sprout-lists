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
	 * @return array
	 */
	public function defineAttributes()
	{
		return array(
			'type'      => AttributeType::String,
			'name'      => array(AttributeType::String, 'required' => true),
			'handle'    => array(AttributeType::String, 'required' => true),
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

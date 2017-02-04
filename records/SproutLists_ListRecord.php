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
			'name'   => AttributeType::String,
			'handle' => AttributeType::String,
			'total'  => AttributeType::Number
		);
	}

	public function defineRelations()
	{
		return array(
			'element' => array(
				static::BELONGS_TO,
				'ElementRecord',
				'id',
				'required' => true,
				'onDelete' => static::CASCADE
			),
			'subscribers' => array(
				static::MANY_MANY,
				'SproutLists_SubscriberRecord',
				'sproutlists_lists_subscribers(listId, subscriberId)'
			)
		);
	}
}

<?php
namespace Craft;

class SproutLists_SubscriberRecord extends BaseRecord
{	
	/**
	 * Return table name corresponding to this record
	 *
	 * @return string
	 */
	public function getTableName()
	{
		return 'sproutlists_subscribers';
	}

	/**
	 * These have to be explicitly defined in order for the plugin to install
	 *
	 * @return array
	 */
	public function defineAttributes()
	{
		return array(
			'userId'      => AttributeType::Number,
			'email'       => array(AttributeType::Email, 'required' => true, 'unique' => true),
			'firstName'   => AttributeType::String,
			'lastName'    => AttributeType::String,
			'type'        => AttributeType::String,
			'details'     => AttributeType::String,
			'dateCreated' => AttributeType::DateTime,
			'count'       => AttributeType::Number
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
			'subscriberLists' => array(
				static::MANY_MANY,
				'SproutLists_ListRecord',
				'sproutlists_subscriptions(subscriberId, listId)'
			)
		);
	}
}

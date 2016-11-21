<?php
namespace Craft;

class SproutLists_RecipientRecord extends BaseRecord
{	
	/**
	 * Return table name corresponding to this record
	 * @return string
	 */
	public function getTableName()
	{
		return 'sproutlists_reipients';
	}

	/**
	 * These have to be explicitly defined in order for the plugin to install
	 * @return array
	 */
	public function defineAttributes()
	{
		return array(
			'email'       => AttributeType::String,
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
			'recipientLists' => array(
				static::MANY_MANY,
				'SproutLists_ListsRecord',
				'sproutlists_lists_recipients(recipientId, listId)'
			)
		);
	}
}

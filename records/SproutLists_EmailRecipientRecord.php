<?php
namespace Craft;

class SproutLists_EmailRecipientRecord extends BaseRecord
{	
	/**
	 * Return table name corresponding to this record
	 * @return string
	 */
	public function getTableName()
	{
		return 'sproutlists_emails';
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
			'elementId'   => AttributeType::Number,
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
			'elementObject' => array(
				static::BELONGS_TO,
				'ElementRecord',
				'elementId',
				'required' => true,
				'onDelete' => static::CASCADE
			),
			'list' => array(
				static::BELONGS_TO, 
				'SproutLists_ListsRecord',
				'required' => true, 
				'onDelete' => static::CASCADE
			),
		);
	}
}

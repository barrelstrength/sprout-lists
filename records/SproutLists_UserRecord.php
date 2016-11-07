<?php
namespace Craft;

class SproutLists_UserRecord extends BaseRecord
{	
	/**
	 * Return table name corresponding to this record
	 * @return string
	 */
	public function getTableName()
	{
		return 'sproutlists_users';
	}

	/**
	 * These have to be explicitly defined in order for the plugin to install
	 * @return array
	 */
	public function defineAttributes()
	{
		return array(
			'list'        => AttributeType::Number,
			'userId'      => AttributeType::Number,
			'elementId'   => AttributeType::Number,
			'dateCreated' => AttributeType::DateTime,
			'count'       => AttributeType::Number
		);
	}

	public function defineRelations()
	{
		return array(
			'element'        => array(
				static::BELONGS_TO,
				'ElementRecord',
				'id',
				'required' => true,
				'onDelete' => static::CASCADE
			),
			'user' => array(
				static::BELONGS_TO, 
				'UserRecord', 
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

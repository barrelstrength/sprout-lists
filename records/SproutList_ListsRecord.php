<?php
namespace Craft;

class SproutList_ListsRecord extends BaseRecord
{	
	/**
	 * Return table name corresponding to this record
	 * @return string
	 */
	public function getTableName()
	{
		return 'sproutlist_lists';
	}

	/**
	 * These have to be explicitly defined in order for the plugin to install
	 * @return array
	 */
	public function defineAttributes()
	{
		return array(
			'name'   => AttributeType::String,
			'handle' => AttributeType::String,
		);
	}

	public function defineRelations()
	{
	   return array();
	}
}

<?php
namespace Craft;

class SproutLists_ListsRecord extends BaseRecord
{	
	/**
	 * Return table name corresponding to this record
	 * @return string
	 */
	public function getTableName()
	{
		return 'sproutlists_lists';
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
		return array(
			'emailRecipients' => array(
				static::MANY_MANY,
				'SproutLists_EmailRecipientRecord',
				'sproutlists_lists_recipients(listId, recipientId)'
			)
		);
	}
}

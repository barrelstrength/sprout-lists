<?php
namespace Craft;

/**
 * Class SproutEmail_DefaultMailerRecipientListRecipientRecord
 *
 * @package Craft
 *
 * @property int $recipientId
 * @property int $recipientListId
 */
class SproutLists_ListsRecipientsRecord extends BaseRecord
{
	public function getTableName()
	{
		return 'sproutlists_lists_recipients';
	}

	public function defineAttributes()
	{
		return array(
			'recipientId'     => AttributeType::Number,
			'listId' => AttributeType::Number,
		);
	}

	public function defineIndexes()
	{
		return array(
			array('columns' => array('recipientId', 'listId'), 'unique' => true)
		);
	}
}

<?php
namespace Craft;

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

	public function defineRelations()
	{
		return array(
			'listElements' => array(
				static::HAS_MANY,
				'SproutLists_ListsElementsRelationsRecord',
				'listId',
				'required' => true,
				'onDelete' => static::CASCADE
			)
		);
	}

	public function defineIndexes()
	{
		return array(
			array('columns' => array('recipientId', 'listId'), 'unique' => true)
		);
	}
}

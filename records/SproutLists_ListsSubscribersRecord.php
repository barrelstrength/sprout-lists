<?php
namespace Craft;

class SproutLists_ListsSubscribersRecord extends BaseRecord
{
	public function getTableName()
	{
		return 'sproutlists_lists_subscribers';
	}

	public function defineAttributes()
	{
		return array(
			'subscriberId'     => AttributeType::Number,
			'listId' => AttributeType::Number,
		);
	}

	public function defineRelations()
	{
		return array(
			'listElements' => array(
				static::HAS_MANY,
				'SproutLists_SubscriptionsRecord',
				'listId',
				'required' => true,
				'onDelete' => static::CASCADE
			)
		);
	}

	public function defineIndexes()
	{
		return array(
			array('columns' => array('subscriberId', 'listId'), 'unique' => true)
		);
	}
}

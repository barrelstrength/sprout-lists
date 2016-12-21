<?php
namespace Craft;

class SproutLists_ListsElementsRelationsRecord extends BaseRecord
{
	/**
	 * @return string
	 */
	public function getTableName()
	{
		return 'sproutlists_lists_subscribers_elements';
	}

	/**
	 * @return array
	 */
	public function defineAttributes()
	{
		return array(
			'listId' => AttributeType::Number,
			'type'   => AttributeType::String
		);
	}

	public function defineRelations()
	{
		return array(
			'element' => array(
				static::BELONGS_TO,
				'ElementRecord',
				'elementId',
				'required' => true,
				'onDelete' => static::CASCADE
			),
			'list'    => array(
				static::BELONGS_TO,
				'SproutLists_ListsRecord',
				'listId',
				'required' => true,
				'onDelete' => static::CASCADE
			)
		);
	}

	public function defineIndexes()
	{
		return array(
			array('columns' => array('elementId'))
		);
	}
}

<?php
namespace Craft;

class SproutLists_ListsElementsRelationsModel extends BaseModel
{
	public function defineAttributes()
	{
		return array(
			'id'        => AttributeType::Number,
			'type'      => AttributeType::String,
			'elementId' => AttributeType::Number,
			'listId'    => AttributeType::Number
		);
	}
}

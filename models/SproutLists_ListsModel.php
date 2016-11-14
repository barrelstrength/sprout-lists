<?php
namespace Craft;

class SproutLists_ListsModel extends BaseModel
{
	public function defineAttributes()
	{
		return array(
			'id'     => AttributeType::Number,
			'name'   => AttributeType::String,
			'handle' => AttributeType::String,
		);
	}
}

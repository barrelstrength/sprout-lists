<?php
namespace Craft;

class SproutLists_ListsModel extends BaseModel
{
	public function defineAttributes()
	{
		return array(
			'id'     => AttributeType::Number,
			'name'   => array(AttributeType::String, 'required' => true),
			'handle' => array(AttributeType::String, 'required' => true),
		);
	}
}

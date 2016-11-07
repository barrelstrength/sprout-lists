<?php
namespace Craft;

class SproutLists_EmailModel extends BaseModel
{
	public function defineAttributes()
	{
		return array(
			'id'          => AttributeType::Number,
			'list'        => AttributeType::Number,
			'email'       => AttributeType::String,
			'firstName'   => AttributeType::String,
			'lastName'    => AttributeType::String,
			'details'     => AttributeType::String,
			'elementId'   => AttributeType::Number,
			'dateCreated' => AttributeType::DateTime,
			'count'       => AttributeType::Number
		);
	}
}

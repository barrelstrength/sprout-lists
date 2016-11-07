<?php
namespace Craft;

class SproutLists_EmailModel extends BaseElementModel
{
	protected $elementType = 'SproutEmail_ListElementType';

	public function defineAttributes()
	{
		$defaults = parent::defineAttributes();

		$attributes = array(
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

		return array_merge($defaults, $attributes);
	}
}

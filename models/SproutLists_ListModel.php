<?php
namespace Craft;

class SproutLists_ListModel extends BaseElementModel
{
	protected $elementType = 'SproutLists_List';

	public function defineAttributes()
	{
		$defaults = parent::defineAttributes();

		$attributes = array(
			'id'     => AttributeType::Number,
			'name'   => array(AttributeType::String, 'required' => true),
			'handle' => array(AttributeType::String, 'required' => true),
			'total'  => array(AttributeType::Number),
		);

		return array_merge($defaults, $attributes);
	}

	public function __toString()
	{
		return $this->id;
	}
}

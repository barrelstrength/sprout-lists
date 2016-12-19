<?php
namespace Craft;

class SproutLists_ListsModel extends BaseElementModel
{
	protected $elementType = 'SproutLists_Lists';

	public function defineAttributes()
	{
		$defaults = parent::defineAttributes();

		$attributes = array(
			'id'     => AttributeType::Number,
			'name'   => array(AttributeType::String, 'required' => true),
			'handle' => array(AttributeType::String, 'required' => true),
		);

		return array_merge($defaults, $attributes);
	}

	public function __toString()
	{
		return $this->id;
	}
}

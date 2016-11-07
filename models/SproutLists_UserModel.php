<?php
namespace Craft;

class SproutLists_UserModel extends BaseElementModel
{
	protected $elementType = 'SproutLists_List';
	/**
	 * @access protected
	 * @return array
	 */
	protected function defineAttributes()
	{
		$defaults = parent::defineAttributes();

		$attributes = array(
			'id'          => AttributeType::Number,
			'list'        => AttributeType::Number,
			'userId'      => AttributeType::Number,
			'elementId'   => AttributeType::Number,
			'dateCreated' => AttributeType::DateTime,
			'count'       => AttributeType::Number
		);

		return array_merge($defaults, $attributes);
	}
}
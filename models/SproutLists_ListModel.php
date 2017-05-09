<?php
namespace Craft;

class SproutLists_ListModel extends BaseElementModel
{
	/**
	 * @var string
	 */
	protected $elementType = 'SproutLists_List';

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->name;
	}

	/**
	 * @return array
	 */
	public function defineAttributes()
	{
		$defaults = parent::defineAttributes();

		$attributes = array(
			'id'        => array(AttributeType::Number),
			'type'      => array(AttributeType::String),
			'name'      => array(AttributeType::String, 'required' => true),
			'handle'    => array(AttributeType::String, 'required' => true),
			'total'     => array(AttributeType::Number),
		);

		return array_merge($defaults, $attributes);
	}

	/**
	 * @return false|string
	 */
	public function getCpEditUrl()
	{
		return UrlHelper::getCpUrl('sproutlists/lists/edit/' . $this->id);
	}
}

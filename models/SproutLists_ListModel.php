<?php

namespace Craft;

/**
 * Class SproutLists_ListModel
 *
 * @package Craft
 * --
 * @property int $id
 * @property int $elementId
 * @property string $type
 * @property string $name
 * @property string $handle
 * @property int $totalSubscribers
 */
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
			'id'               => array(AttributeType::Number),
			'elementId'        => array(AttributeType::Number),
			'type'             => array(AttributeType::String),
			'name'             => array(AttributeType::String, 'required' => true),
			'handle'           => array(AttributeType::String, 'required' => true),
			'totalSubscribers' => array(AttributeType::Number),
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

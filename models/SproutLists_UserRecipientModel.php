<?php
namespace Craft;

class SproutLists_UserRecipientModel extends BaseElementModel
{
	protected $elementType = 'SproutLists_UserRecipient';
	/**
	 * @access protected
	 * @return array
	 */
	protected function defineAttributes()
	{
		$defaults = parent::defineAttributes();

		$attributes = array(
			'id'          => AttributeType::Number,
			'listId'      => AttributeType::Number,
			'userId'      => AttributeType::Number,
			'elementId'   => AttributeType::Number,
			'dateCreated' => AttributeType::DateTime,
			'count'       => AttributeType::Number
		);

		return array_merge($defaults, $attributes);
	}

	public function __toString()
	{
		$user = craft()->users->getUserById($this->userId);
		return $user->email;
	}
}
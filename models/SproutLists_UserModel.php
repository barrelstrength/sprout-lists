<?php
namespace Craft;

class SproutLists_UserModel extends BaseModel
{
	/**
	 * @access protected
	 * @return array
	 */
	protected function defineAttributes()
	{
		return array(
			'list'        => AttributeType::String,
			'userId'      => AttributeType::String,
			'elementId'   => AttributeType::String,
			'dateCreated' => AttributeType::DateTime,
			'count'       => AttributeType::String
		);
	}
}
<?php
namespace Craft;

class SproutSubscribe_SubscriptionModel extends BaseModel
{
	/**
	 * @access protected
	 * @return array
	 */
	protected function defineAttributes()
	{
		return array(
			'userId'      => AttributeType::String,
			'elementId'   => AttributeType::String,
			'dateCreated' => AttributeType::DateTime,
			'count'       => AttributeType::String
		);
	}
}
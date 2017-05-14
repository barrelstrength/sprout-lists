<?php
namespace Craft;

class SproutLists_SubscriptionModel extends BaseModel
{
	/**
	 * @return array
	 */
	public function defineAttributes()
	{
		return array(
			// Subscriber Info
			'email'        => AttributeType::String,
			'userId'       => AttributeType::Number,
			'subscriberId' => AttributeType::Number,

			// List Info
			'list'         => AttributeType::Mixed,
			'elementId'    => AttributeType::Number,
			'listId'       => AttributeType::Number
		);
	}
}

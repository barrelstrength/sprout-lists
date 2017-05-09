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
			'listId'       => AttributeType::Number,

			// Other
			'type'         => AttributeType::String,
		);
	}
}

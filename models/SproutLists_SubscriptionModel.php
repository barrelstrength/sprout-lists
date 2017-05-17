<?php

namespace Craft;

/**
 * Class SproutLists_SubscriptionModel
 *
 * @package Craft
 * --
 * @property int    $id
 * @property string $listType
 * @property mixed  $listHandle
 * @property int    $listId
 * @property int    $elementId
 * @property int    $subscriberId
 * @property int    $userId
 * @property string $email
 */
class SproutLists_SubscriptionModel extends BaseModel
{
	/**
	 * @return array
	 */
	public function defineAttributes()
	{
		return array(
			'id'           => array(AttributeType::Number),

			// List Info
			'listType'     => AttributeType::String,
			'listHandle'   => AttributeType::Mixed,
			'listId'       => AttributeType::Number,
			'elementId'    => AttributeType::Number,

			// Subscriber Info
			'subscriberId' => AttributeType::Number,
			'email'        => AttributeType::String,
			'userId'       => AttributeType::Number,
		);
	}
}

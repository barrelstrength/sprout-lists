<?php
namespace Craft;

class SproutSubscribeService extends BaseApplicationComponent
{
	public function isSubscribed($userId, $elementId)
	{
		if (!$userId or !$elementId)
		{
			return false;
		}

		$query = craft()->db->createCommand()
										->select('userId, elementId')
										->from('sproutsubscribe_subscriptions')
										->where(array(
											'AND', 
											'userId = :userId', 
											'elementId = :elementId'
										), array(
											':userId' => $userId, 
											':elementId' => $elementId
										))->queryRow();
		
		return (is_array($query)) ? true : false;
	}

	public function subscriptionIds($userId, $elementType, $criteria)
	{
		$userId = craft()->userSession->id;
	
		if (!$userId)
		{
			return false;
		}

		// join the sproutsubscribe_subscriptions and elements table to make sure we're only
		// getting back the IDs of the Elements that match our type.

		$results = craft()->db->createCommand()
						->select('elementId')
						->from('sproutsubscribe_subscriptions')
						->where('userId = :userId', array(
							':userId' => $userId
						))->queryAll();

		$ids = "";

		foreach ($results as $key => $value) 
		{	    	
			if ($ids == "") 
			{
					$ids = $value['elementId'];
			}
			else
			{ 
					$ids .= "," . $value['elementId'];
			}
		}

		return $ids;  		
	}

	public function subscribe($userId, $elementId)
	{
		$record = new SproutSubscribe_SubscriptionRecord;
		$record->userId = $userId;
		$record->elementId = $elementId;

		return $record->save();
	}

	public function unsubscribe($userId, $elementId)
	{
		$result = craft()->db->createCommand()
										 ->delete('sproutsubscribe_subscriptions', array(
												'userId' => $userId,
												'elementId' => $elementId
										 ));

		return $result;
	}
}


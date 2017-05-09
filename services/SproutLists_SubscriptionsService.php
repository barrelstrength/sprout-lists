<?php
namespace Craft;

class SproutLists_SubscriptionsService extends BaseApplicationComponent
{
	/**
	 * @todo - this should probably be delegated to the $listType class as well.
	 * @todo - clarify that this takes a SproutLists_SubscriberModel with an array of listIds as an attribute
	 */
	public function saveSubscriptions(SproutLists_SubscriberModel $model, $sync = true)
	{
		$subscriberId      = $model->id;
		$subscriberListIds = $model->subscriberLists;

		if ($sync === true)
		{
			try
			{
				SproutLists_SubscriptionsRecord::model()->deleteAll('subscriberId = :subscriberId', array(
					':subscriberId' => $subscriberId
				));
			}
			catch (Exception $e)
			{
				SproutListsPlugin::log($e->getMessage(), LogLevel::Error);
			}
		}

		$records = array();

		if (!empty($subscriberListIds))
		{
			foreach ($subscriberListIds as $listId)
			{
				$list = sproutLists()->lists->getListById($listId);

				if ($list)
				{
					$relation = new SproutLists_SubscriptionsRecord();

					$relation->subscriberId = $subscriberId;
					$relation->listId       = $list->id;
					// Default type
					$relation->type         = "subscriber";

					$result = $relation->save(false);

					$records[] = $relation->id;

					if (!$result)
					{
						throw new Exception(print_r($relation->getErrors(), true));
					}
				}
				else
				{
					throw new Exception(Craft::t('The Subscriber List with id {listId} does not exists.', array(
						'listId' => $listId)
					));
				}
			}
		}

		sproutLists()->subscribers->updateTotalSubscribersCount();

		return $records;
	}
}
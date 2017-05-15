<?php

namespace Craft;

class SproutLists_SubscribersService extends BaseApplicationComponent
{
	/**
	 * Sync SproutLists subscriber to craft_users if same email is found on save.
	 *
	 * @param Event $event
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function updateUserIdOnSave(Event $event)
	{
		$userId = $event->params['user']->id;
		$email  = $event->params['user']->email;

		// First try to find a user by the Craft User ID
		$subscriberRecord = SproutLists_SubscriberRecord::model()->findByAttributes(array(
			'userId' => $userId
		));

		// If that doesn't work, try to find a user with a matching email address
		if ($subscriberRecord == null)
		{
			$subscriberRecord = SproutLists_SubscriberRecord::model()->findByAttributes(array(
				'userId' => null,
				'email'  => $email
			));

			// Assign the user ID to the subscriber with the matching email address
			$subscriberRecord->userId = $event->params['user']->id;
		}

		if ($subscriberRecord != null)
		{
			// If the user has updated their email, let's also update it for our Subscriber
			$subscriberRecord->email     = $event->params['user']->email;
			$subscriberRecord->firstName = $event->params['user']->firstName;
			$subscriberRecord->lastName  = $event->params['user']->lastName;

			$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;

			try
			{
				if ($subscriberRecord->save(false))
				{
					if ($transaction && $transaction->active)
					{
						$transaction->commit();
					}

					return true;
				}
			}
			catch (\Exception $e)
			{
				if ($transaction && $transaction->active)
				{
					$transaction->rollback();
				}

				throw $e;
			}
		}

		return false;
	}

	/**
	 * Remove any relationships between Sprout Lists Subscribers and Users who are deleted
	 *
	 * @param Event $event
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function updateUserIdOnDelete(Event $event)
	{
		$userId = $event->params['user']->id;

		$subscriberRecord = SproutLists_SubscriberRecord::model()->findByAttributes(array(
			'userId' => $userId,
		));

		if ($subscriberRecord != null)
		{
			$subscriberRecord->userId = null;

			$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;

			try
			{
				if ($subscriberRecord->save(false))
				{
					if ($transaction && $transaction->active)
					{
						$transaction->commit();
					}

					return true;
				}
			}
			catch (\Exception $e)
			{
				if ($transaction && $transaction->active)
				{
					$transaction->rollback();
				}

				throw $e;
			}
		}

		return false;
	}
}
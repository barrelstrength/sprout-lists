<?php

namespace Craft;

class SproutLists_MailchimpListType extends SproutListsBaseListType
{
	private $apiKey = null;

	public function __construct()
	{
		$settings = $this->getSettings();

		if (empty($settings['mailchimp']['apiKey']))
		{
			throw new \Exception('Mailchimp API key needed.');
		}

		$this->apiKey = $settings['mailchimp']['apiKey'];
	}

	public function getSettingsHtml($settings = null)
	{
		return craft()->templates->render('sproutlists/integrations/mailchimp/settings', array(
			'settings' => $settings
		));
	}

	/**
	 * Subscribe a user to a list for this List Type
	 *
	 * @param $user
	 *
	 * @return mixed
	 */
	public function subscribe($subscription)
	{
		$client = new \Mailchimp($this->apiKey);

		$lists = $client->lists
			->subscribe($subscription->listId, ['email' => $subscription->email], null, 'html', false);

		if (!empty($lists))
		{
			return true;
		}

		return false;
	}

	/**
	 * Unsubscribe a user from a list for this List Type
	 *
	 * @param $user
	 *
	 * @return mixed
	 */
	public function unsubscribe($subscription)
	{
		// TODO: Implement unsubscribe() method.
	}

	/**
	 * Check if a user is subscribed to a list
	 *
	 * @param $criteria
	 *
	 * @return mixed
	 */
	public function isSubscribed($subscription)
	{
		$client = new \Mailchimp($this->apiKey);

		$email = $subscription->email;

		if (!is_array($email))
		{
			$email = array('email' => $email);
		}

		$result = $client->lists->memberInfo($subscription->listId, array($email));

		return $result['success_count'];
	}

	/**
	 * Return all lists for a given subscriber.
	 *
	 * @param $criteria
	 *
	 * @return mixed
	 */
	public function getLists($subscriber)
	{
		// TODO: Implement getLists() method.
	}

	/**
	 * Get subscribers on a given list.
	 *
	 * @param $list
	 *
	 * @return mixed
	 * @internal param $criteria
	 *
	 */
	public function getSubscribers($list)
	{
		// TODO: Implement getSubscribers() method.
	}
}
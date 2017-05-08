<?php
namespace Craft;

class SproutLists_SubscriberModel extends BaseElementModel
{
	protected $elementType = 'SproutLists_Subscriber';

	protected $subscriberListsIds;

	/**
	 * @return string
	 */
	public function __toString()
	{
		if ($this->email != null)
		{
			return $this->email;
		}

		if ($this->userId != null)
		{
			$user = craft()->users->getUserById($this->userId);

			return $user->email;
		}
	}

	/**
	 * @return array
	 */
	public function defineAttributes()
	{
		$defaults = parent::defineAttributes();

		$attributes = array(
			'id'              => AttributeType::Number,
			'email'           => array(AttributeType::Email),
			'userId'          => array(AttributeType::Number),
			'firstName'       => AttributeType::String,
			'lastName'        => AttributeType::String,
			'subscriberLists' => array(AttributeType::Mixed),
			'details'         => AttributeType::String,
			'dateCreated'     => AttributeType::DateTime,

			// List Name
			'name'            => AttributeType::String
		);

		return array_merge($defaults, $attributes);
	}

	/**
	 * @return false|string
	 */
	public function getCpEditUrl()
	{
		return UrlHelper::getCpUrl('sproutlists/subscribers/edit/' . $this->id);
	}

	/**
	 * @return array
	 */
	public function getSubscriberListIds()
	{
		if (is_null($this->subscriberListsIds))
		{
			$this->subscriberListsIds = array();

			$subscriberLists = $this->getSubscriberLists();

			if (count($subscriberLists))
			{
				foreach ($subscriberLists as $list)
				{
					$this->subscriberListsIds[] = $list->id;
				}
			}
		}

		return $this->subscriberListsIds;
	}

	/**
	 * @todo - Improve clarity of method names. Not clear.
	 *
	 * @return mixed
	 */
	public function getSubscriberLists()
	{
		return sproutLists()->lists->getListsBySubscriberId($this->id);
	}

	/**
	 * @return mixed
	 */
	public function getSubscriberListsHtml()
	{
		$id = isset($this->id) ? $this->id : null;

		$element = new SproutLists_SubscriberModel;

		if ($id != null)
		{
			$element = sproutLists()->subscribers->getSubscriberById($id);
		}

		$values = array();

		if (count($element->getSubscriberListIds()))
		{
			$values = $element->getSubscriberListIds();
		}

		return sproutLists()->lists->getSubscriberListsHtml($values);
	}
}

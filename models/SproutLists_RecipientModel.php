<?php
namespace Craft;

class SproutLists_RecipientModel extends BaseElementModel
{
	protected $elementType = 'SproutLists_Recipient';
	protected $recipientListsIds;

	public function defineAttributes()
	{
		$defaults = parent::defineAttributes();

		$attributes = array(
			'id'              => AttributeType::Number,
			'email'           => array(AttributeType::String),
			'userId'          => array(AttributeType::Number),
			'firstName'       => AttributeType::String,
			'lastName'        => AttributeType::String,
			'recipientLists'  => array(AttributeType::Mixed),
			'details'         => AttributeType::String,
			'dateCreated'     => AttributeType::DateTime
		);

		return array_merge($defaults, $attributes);
	}

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
	public function getRecipientListIds()
	{
		if (is_null($this->recipientListsIds))
		{
			$this->recipientListsIds = array();
			$recipientLists = $this->getRecipientLists();

			if (count($recipientLists))
			{
				foreach ($recipientLists as $list)
				{
					$this->recipientListsIds[] = $list->id;
				}
			}
		}

		return $this->recipientListsIds;
	}

	public function getRecipientLists()
	{
		return sproutLists()->getListsByRecipientId($this->id);
	}

	public function getRecipientListsHtml()
	{
		$id = isset($this->id) ? $this->id : null;

		$element = new SproutLists_RecipientModel;

		if ($id != null)
		{
			$element = sproutLists()->listRecipient->getRecipientById($id);
		}

		return sproutLists()->getRecipientListsHtml($element);
	}
}

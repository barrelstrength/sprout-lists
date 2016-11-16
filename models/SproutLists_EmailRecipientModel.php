<?php
namespace Craft;

class SproutLists_EmailRecipientModel extends BaseElementModel
{
	protected $elementType = 'SproutLists_EmailRecipient';
	protected $recipientListsIds;

	public function defineAttributes()
	{
		$defaults = parent::defineAttributes();

		$attributes = array(
			'id'          => AttributeType::Number,
			'listId'      => AttributeType::Number,
			'email'       => array(AttributeType::String, "required" => true),
			'firstName'   => AttributeType::String,
			'lastName'    => AttributeType::String,
			'details'     => AttributeType::String,
			'elementId'   => AttributeType::Number,
			'dateCreated' => AttributeType::DateTime,
			'count'       => AttributeType::Number
		);

		return array_merge($defaults, $attributes);
	}

	public function __toString()
	{
		return $this->email;
	}

	/**
	 * @return array
	 */
	public function getRecipientListIds()
	{
		if (is_null($this->recipientListsIds))
		{
			$this->recipientListsIds = array();
			$recipientLists          = sproutLists()->getAllLists();

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
}

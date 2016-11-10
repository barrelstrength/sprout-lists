<?php
namespace Craft;

class SproutLists_UserRecipientElementType extends BaseElementType
{
	/**
	 * @return string
	 */
	public function getName()
	{
		return Craft::t('Sprout Lists');
	}

	/**
	 * @return bool
	 */
	public function hasTitles()
	{
		return false;
	}

	/**
	 * @return bool
	 */
	public function hasContent()
	{
		return false;
	}

	/**
	 * @return bool
	 */
	public function isLocalized()
	{
		return false;
	}


	/**
	 * @param null $context
	 *
	 * @return array
	 */
	public function getSources($context = null)
	{

		$sources = array(
			'*' => array(
				'label' => Craft::t('All Lists'),
			),
		);

		return $sources;
	}

	public function modifyElementsQuery(DbCommand $query, ElementCriteriaModel $criteria)
	{
		$query->addSelect('userlists.*')
			->join('sproutlists_users userlists', 'userlists.id = elements.id')
			->join('users users', 'users.id = userlists.userId')
			->join('sproutlists_lists lists', 'lists.id = userlists.listId');

		if ($criteria->order)
		{
			//$criteria->order = $criteria->order . 'x';
			// Sort by list name not by listId
			if (stripos($criteria->order, "listId") !== false)
			{
				$criteria->order = str_replace("listId", "lists.name", $criteria->order);
			}

			// Sort by user email not by userId
			if (preg_match('/id (.*)/', $criteria->order))
			{
				$criteria->order = str_replace("id", "users.email", $criteria->order);
			}

			// Trying to order by date creates ambiguity errors
			// Let's make sure mysql knows what we want to sort by
			if (stripos($criteria->order, 'elements.') === false)
			{
				$criteria->order = str_replace('dateCreated', 'userlists.dateCreated', $criteria->order);
				$criteria->order = str_replace('dateUpdated', 'userlists.dateUpdated', $criteria->order);
			}
		}
	}

	public function getTableAttributeHtml(BaseElementModel $element, $attribute)
	{
		switch ($attribute)
		{
			case "listId":
				$list = SproutLists_ListsRecord::model()->findById($element->listId);

				if ($list)
				{
					return $list->name;
				}

				break;

			case "elementId":
					$listElement = craft()->elements->getElementById($element->elementId);

					if (!empty($listElement) && !empty($listElement->title))
					{
						return $listElement->title;
					}

					return $element->elementId;
				break;

			default:
			{
				return parent::getTableAttributeHtml($element, $attribute);
			}
		}
	}

	public function defineAvailableTableAttributes()
	{
		$attributes = array(
			'id'          => array('label' => Craft::t('Email')),
			'listId'      => array('label' => Craft::t('List')),
			'userId'      => array('label' => Craft::t('User ID')),
			'elementId'   => array('label' => Craft::t('Element')),
			'dateCreated' => array('label' => Craft::t('Date Created')),
			'dateUpdated' => array('label' => Craft::t('Date Updated'))
		);

		return $attributes;
	}

	public function defineCriteriaAttributes()
	{
		return array(
			'userId'    => AttributeType::Number,
			'elementId' => AttributeType::Number,
			'listId'    => AttributeType::Number
		);
	}

	public function defineSearchableAttributes()
	{
		return array('userId');
	}

	public function getDefaultTableAttributes($source = null)
	{
		$attributes = array();

		$attributes[] = 'listId';
		$attributes[] = 'userId';
		$attributes[] = 'elementId';
		$attributes[] = 'dateCreated';
		$attributes[] = 'dateUpdated';


		return $attributes;
	}

	public function populateElementModel($row)
	{
		return SproutLists_UserRecipientModel::populateModel($row);
	}
}

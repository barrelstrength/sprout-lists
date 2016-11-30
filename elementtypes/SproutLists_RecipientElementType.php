<?php
namespace Craft;

class SproutLists_RecipientElementType extends BaseElementType
{
	/**
	 * @return string
	 */
	public function getName()
	{
		return Craft::t('Sprout List Recipients');
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

		$lists = sproutLists()->listRecipient->getLists();

		if (!empty($lists))
		{
			foreach ($lists as $list)
			{
				$key = 'lists:' . $list->id;

				$sources[$key] = array(
					'label'    => $list->name,
					'data'     => array('handle' => $list->handle),
					'criteria' => array('listId' => $list->id)
				);
			}
		}

		return $sources;
	}

	public function modifyElementsQuery(DbCommand $query, ElementCriteriaModel $criteria)
	{
		$query->addSelect('recipients.*')
			->join('sproutlists_recipients recipients', 'recipients.id = elements.id')
			->join('sproutlists_lists_recipients listsrecipients', 'listsrecipients.recipientId = recipients.id')
			->join('sproutlists_lists lists', 'lists.id = listsrecipients.listId');

		if ($criteria->order)
		{
			//$criteria->order = $criteria->order . 'x';
			// Sort by list name not by listId
			if (stripos($criteria->order, "listId") !== false)
			{
				$criteria->order = str_replace("listId", "lists.name", $criteria->order);
			}

			// Trying to order by date creates ambiguity errors
			// Let's make sure mysql knows what we want to sort by
			if (stripos($criteria->order, 'elements.') === false)
			{
				$criteria->order = str_replace('dateCreated', 'recipients.dateCreated', $criteria->order);
				$criteria->order = str_replace('dateUpdated', 'recipients.dateUpdated', $criteria->order);
			}
		}

		if ($criteria->listId)
		{
			$query->andWhere(DbHelper::parseParam('lists.id', $criteria->listId, $query->params));
		}
	}

	public function getTableAttributeHtml(BaseElementModel $element, $attribute)
	{
		switch ($attribute)
		{
			case "action":
					return "<a href='edit/" . $element->id . "'>" . Craft::t("Edit") . "</a>";
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
			'userId'      => array('label' => Craft::t('User ID')),
			'action'      => array('label' => ''),
			'dateCreated' => array('label' => Craft::t('Date Created')),
			'dateUpdated' => array('label' => Craft::t('Date Updated'))
		);

		return $attributes;
	}

	public function defineCriteriaAttributes()
	{
		return array(
			'email'     => AttributeType::Number,
			'listId'    => AttributeType::Number
		);
	}

	public function defineSearchableAttributes()
	{
		return array('email');
	}

	public function getDefaultTableAttributes($source = null)
	{
		$attributes = array();

		$attributes[] = 'userId';
		$attributes[] = 'action';
		$attributes[] = 'dateCreated';
		$attributes[] = 'dateUpdated';

		return $attributes;
	}

	public function getAvailableActions($source = null)
	{
		$deleteAction = craft()->elements->getAction('SproutLists_RecipientDelete');

		$deleteAction->setParams(array(
			'confirmationMessage' => Craft::t('Are you sure you want to delete the selected recipients?'),
			'successMessage'      => Craft::t('Recipients deleted.'),
		));

		return array($deleteAction);
	}

	public function populateElementModel($row)
	{
		return SproutLists_RecipientModel::populateModel($row);
	}
}

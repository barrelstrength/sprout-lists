<?php
namespace Craft;

class SproutLists_SubscriberElementType extends BaseElementType
{
	/**
	 * @return string
	 */
	public function getName()
	{
		return Craft::t('Sprout List Subscribers');
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

		$lists = sproutLists()->subscribers->getLists();

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
		$query
			->addSelect('lists.*')
			->addSelect('subscribers.*')
			->join('sproutlists_subscribers subscribers', 'subscribers.id = elements.id')
			->leftJoin('sproutlists_lists_subscribers listssubscribers', 'listssubscribers.subscriberId = subscribers.id')
			->leftJoin('sproutlists_lists lists', 'lists.id = listssubscribers.listId');

		if ($criteria->order)
		{
			// Sort by list name not by listId
			if (stripos($criteria->order, "listId") !== false)
			{
				$criteria->order = str_replace("listId", "lists.name", $criteria->order);
			}

			// Trying to order by date creates ambiguity errors
			// Let's make sure mysql knows what we want to sort by
			if (stripos($criteria->order, 'elements.') === false)
			{
				$criteria->order = str_replace('dateCreated', 'subscribers.dateCreated', $criteria->order);
				$criteria->order = str_replace('dateUpdated', 'subscribers.dateUpdated', $criteria->order);
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
			case "userId":

				if ($element->userId)
				{
					return "<a href='" . UrlHelper::getCpUrl('users/' . $element->userId) . "' class='go'>" . Craft::t('Edit User') . "</a>";
				}

				break;

			default:
				return parent::getTableAttributeHtml($element, $attribute);
				break;
		}
	}

	public function defineAvailableTableAttributes()
	{
		$attributes = array(
			'id'          => array('label' => Craft::t('Email')),
			'userId'      => array('label' => Craft::t('User Account')),
			'dateCreated' => array('label' => Craft::t('Date Created')),
			'dateUpdated' => array('label' => Craft::t('Date Updated'))
		);

		return $attributes;
	}


	public function getDefaultTableAttributes($source = null)
	{
		$attributes = array();

		$attributes[] = 'userId';
		$attributes[] = 'name';
		$attributes[] = 'dateCreated';
		$attributes[] = 'dateUpdated';

		return $attributes;
	}

	public function defineCriteriaAttributes()
	{
		return array(
			'email'  => AttributeType::Number,
			'listId' => AttributeType::Number
		);
	}

	public function defineSearchableAttributes()
	{
		return array('email');
	}

	public function getAvailableActions($source = null)
	{
		$deleteAction = craft()->elements->getAction('SproutLists_SubscriberDelete');

		$deleteAction->setParams(array(
			'confirmationMessage' => Craft::t('Are you sure you want to delete the selected subscribers?'),
			'successMessage'      => Craft::t('Subscribers deleted.'),
		));

		return array($deleteAction);
	}

	public function populateElementModel($row)
	{
		return SproutLists_SubscriberModel::populateModel($row);
	}
}

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

		$lists = sproutLists()->lists->getListSubscribers();

		if (!empty($lists))
		{
			$sources[] = array(
				'heading' => Craft::t('Lists')
			);

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

	/**
	 * @param DbCommand            $query
	 * @param ElementCriteriaModel $criteria
	 *
	 * @return null
	 */
	public function modifyElementsQuery(DbCommand $query, ElementCriteriaModel $criteria)
	{
		$query
			->addSelect('lists.*')
			->addSelect('subscribers.*')
			->join('sproutlists_subscribers subscribers', 'subscribers.id = elements.id')
			->leftJoin('sproutlists_subscriptions subscriptions', 'subscriptions.subscriberId = subscribers.id')
			->leftJoin('sproutlists_lists lists', 'lists.id = subscriptions.listId');

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

	/**
	 * @param BaseElementModel $element
	 * @param string           $attribute
	 *
	 * @return mixed|string
	 */
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

	/**
	 * @return array
	 */
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

	/**
	 * @param null $source
	 *
	 * @return array
	 */
	public function getDefaultTableAttributes($source = null)
	{
		$attributes = array();

		$attributes[] = 'userId';
		$attributes[] = 'name';
		$attributes[] = 'dateCreated';
		$attributes[] = 'dateUpdated';

		return $attributes;
	}

	/**
	 * @return array
	 */
	public function defineCriteriaAttributes()
	{
		return array(
			'email'  => AttributeType::Number,
			'listId' => AttributeType::Number
		);
	}

	/**
	 * @return array
	 */
	public function defineSearchableAttributes()
	{
		return array('email');
	}

	/**
	 * @param null $source
	 *
	 * @return array
	 */
	public function getAvailableActions($source = null)
	{
		$deleteAction = craft()->elements->getAction('SproutLists_SubscriberDelete');

		$deleteAction->setParams(array(
			'confirmationMessage' => Craft::t('Are you sure you want to delete the selected subscribers?'),
			'successMessage'      => Craft::t('Subscribers deleted.'),
		));

		return array($deleteAction);
	}

	/**
	 * @param array $row
	 *
	 * @return BaseModel
	 */
	public function populateElementModel($row)
	{
		return SproutLists_SubscriberModel::populateModel($row);
	}
}

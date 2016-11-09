<?php
namespace Craft;

class SproutLists_ListElementType extends BaseElementType
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
		return true;
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
			->join('sproutlists_users userlists', 'userlists.id = elements.id');
		//	->join('sproutlists_emails emailLists', 'emailLists.id = elements.id');
		//echo '<pre>' . print_r($query, true) . '</pre>'; exit;
		if ($criteria->order)
		{
			// Trying to order by date creates ambiguity errors
			// Let's make sure mysql knows what we want to sort by
			if (stripos($criteria->order, 'elements.') === false)
			{
				$criteria->order = str_replace('dateCreated', 'elements.dateCreated', $criteria->order);
				$criteria->order = str_replace('dateUpdated', 'elements.dateUpdated', $criteria->order);
			}
		}
	}

	public function getTableAttributeHtml(BaseElementModel $element, $attribute)
	{

		switch ($attribute)
		{
			default:
			{
				return parent::getTableAttributeHtml($element, $attribute);
			}
		}
	}

	public function defineAvailableTableAttributes()
	{
		$attributes = array(
			'title'      => array('label' => Craft::t('User')),
			'userId'      => array('label' => Craft::t('User ID')),
			'elementId'      => array('label' => Craft::t('Element ID')),
			'dateCreated' => array('label' => Craft::t('Date Created')),
			'dateUpdated' => array('label' => Craft::t('Date Updated'))
		);

		return $attributes;
	}

	public function defineCriteriaAttributes()
	{
		return array(
			'userId' => AttributeType::Number,
			'elementId' => AttributeType::Number,
		);
	}

	public function defineSearchableAttributes()
	{
		return array('userId');
	}

	public function getDefaultTableAttributes($source = null)
	{
		$attributes = array();

		$attributes[] = 'userId';
		$attributes[] = 'elementId';
		$attributes[] = 'dateCreated';
		$attributes[] = 'dateUpdated';


		return $attributes;
	}

	public function populateElementModel($row)
	{
		return SproutLists_UserModel::populateModel($row);
	}
}

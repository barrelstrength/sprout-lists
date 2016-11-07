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
		$query->addSelect('userLists.*, emailLists.*')
			->join('sproutlists_users userLists', 'userLists.id = elements.id')
			->join('sproutlists_emails emailLists', 'emailLists.id = elements.id');
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
}

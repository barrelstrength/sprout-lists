<?php
namespace Craft;

class SproutLists_ListsElementType extends BaseElementType
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
		$query->addSelect('lists.*')
			->join('sproutlists_lists lists', 'lists.id = elements.id');
	}

	public function defineAvailableTableAttributes()
	{
		$attributes = array(
			'id'          => array('label' => Craft::t('ID')),
			'handle'      => array('label' => Craft::t('List Handle')),
			'name'        => array('label' => Craft::t('List Name')),
			'view'        => array('label' => Craft::t('View Subscribers')),
			'total'       => array('label' => Craft::t('Total Subscribers')),
			'dateCreated' => array('label' => Craft::t('Date Created')),
			'dateUpdated' => array('label' => Craft::t('Date Updated'))
		);

		return $attributes;
	}

	public function getDefaultTableAttributes($source = null)
	{
		$attributes = array();

		$attributes[] = 'id';
		$attributes[] = 'handle';
		$attributes[] = 'name';
		$attributes[] = 'view';
		$attributes[] = 'total';
		$attributes[] = 'dateCreated';
		$attributes[] = 'dateUpdated';

		return $attributes;
	}

	public function getTableAttributeHtml(BaseElementModel $element, $attribute)
	{
		$count = $element->total;

		switch ($attribute)
		{
			case "view":

				if ($element->id && $count > 0)
				{
					return "<a href='" . UrlHelper::getCpUrl('sproutlists/subscribers/' . $element->handle) . "'>" . Craft::t('View 
					Subscribers') .	"</a>";
				}

				break;

			default:
				return parent::getTableAttributeHtml($element, $attribute);
				break;
		}
	}


	public function populateElementModel($row)
	{
		return SproutLists_ListsModel::populateModel($row);
	}
}

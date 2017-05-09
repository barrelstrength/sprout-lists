<?php
namespace Craft;

class SproutLists_ListElementType extends BaseElementType
{
	/**
	 * @return string
	 */
	public function getName()
	{
		return Craft::t('Sprout List');
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

	/**
	 * @param DbCommand            $query
	 * @param ElementCriteriaModel $criteria
	 *
	 * @return null
	 */
	public function modifyElementsQuery(DbCommand $query, ElementCriteriaModel $criteria)
	{
		$query->addSelect('lists.*')
			->join('sproutlists_lists lists', 'lists.id = elements.id');
	}

	/**
	 * @return array
	 */
	public function defineAvailableTableAttributes()
	{
		$attributes = array(
			'name'        => array('label' => Craft::t('List Name')),
			'handle'      => array('label' => Craft::t('List Handle')),
			'view'        => array('label' => Craft::t('View Subscribers')),
			'total'       => array('label' => Craft::t('Total Subscribers')),
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

		$attributes[] = 'name';
		$attributes[] = 'handle';
		$attributes[] = 'view';
		$attributes[] = 'total';
		$attributes[] = 'dateCreated';
		$attributes[] = 'dateUpdated';

		return $attributes;
	}

	/**
	 * @param BaseElementModel $element
	 * @param string           $attribute
	 *
	 * @return mixed|string
	 */
	public function getTableAttributeHtml(BaseElementModel $element, $attribute)
	{
		$count = $element->total;

		switch ($attribute)
		{
			case "handle":

				return "<code>" . $element->handle . "</code>";

				break;

			case "view":

				if ($element->id && $count > 0)
				{
					return "<a href='" . UrlHelper::getCpUrl('sproutlists/subscribers/' . $element->handle) . "' class='go'>" . Craft::t('View Subscribers') .	"</a>";
				}

				break;

			default:
				return parent::getTableAttributeHtml($element, $attribute);

				break;
		}
	}


	/**
	 * @param null $source
	 *
	 * @return array
	 */
	public function getAvailableActions($source = null)
	{
		$deleteAction = craft()->elements->getAction('SproutLists_ListDelete');

		$deleteAction->setParams(array(
			'confirmationMessage' => Craft::t('Are you sure you want to delete the selected lists?'),
			'successMessage'      => Craft::t('Lists deleted.'),
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
		return SproutLists_ListModel::populateModel($row);
	}
}

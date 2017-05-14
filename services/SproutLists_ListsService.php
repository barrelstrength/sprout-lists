<?php

namespace Craft;

class SproutLists_ListsService extends BaseApplicationComponent
{
	/**
	 * Registered List Types
	 *
	 * @var array
	 */
	protected $listTypes = array();

	/**
	 * Initializes the application component.
	 *
	 * This method will load all available List Types
	 *
	 * @return null
	 */
	public function init()
	{
		parent::init();

		$this->getAllListTypes();
	}

	/**
	 * Gets all registered list types.
	 *
	 * @return array
	 */
	public function getAllListTypes()
	{
		$registeredListTypes = craft()->plugins->call('registerSproutListsListTypes');

		if ($registeredListTypes)
		{
			foreach ($registeredListTypes as $plugin => $listTypes)
			{
				foreach ($listTypes as $listType)
				{
					if ($listType && $listType instanceof SproutListsBaseListType)
					{
						$this->listTypes[$listType->getClassName()] = $listType;
					}
				}
			}
		}

		return $this->listTypes;
	}

	/**
	 * Returns a new List Type Class for the given List Type
	 *
	 * @param $type
	 *
	 * @return SproutListsBaseListType
	 * @throws \Exception
	 */
	public function getListType($type = 'subscriber')
	{
		$className = 'SproutLists_' . ucwords($type) . 'ListType';

		$namespace = 'Craft\\' . $className;

		if (!isset($this->listTypes[$className]))
		{
			throw new \Exception('Invalid List Type.');
		}

		return new $namespace;
	}
}
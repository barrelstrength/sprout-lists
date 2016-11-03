<?php

namespace Craft;

class SproutListsService extends BaseApplicationComponent
{
	protected $listTypes = array();
	public $listUser;

	public function init()
	{
		parent::init();

		$this->getAllListTypes();

		$this->listUser = Craft::app()->getComponent('sproutLists_user');
	}

	public function getAllListTypes()
	{
		$classesToLoad = craft()->plugins->call('registerSproutListsListType');

		$types = array();

		if ($classesToLoad)
		{
			foreach ($classesToLoad as $plugin => $classes)
			{
				foreach ($classes as $class)
				{
					if ($class && $class instanceof SproutListsBaseListType)
					{
						$this->listTypes[$class->getClassName()] = $class;

					}
				}
			}
		}

		ksort($this->listTypes);

		return $this->listTypes;
	}

	public function getListType($type)
	{
		$type = ucwords($type);

		$className = 'SproutLists_' . $type . 'ListType';

		$namespace = 'Craft\\' . $className;

		if (!isset($this->listTypes[$className]))
		{
			throw new \Exception("List type invalid.");
		}

		return new $namespace;
	}
}
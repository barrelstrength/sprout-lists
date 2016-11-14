<?php

namespace Craft;

class SproutListsService extends BaseApplicationComponent
{
	protected $listTypes = array();

	public $listUser;
	public $listEmail;

	/**
	 * @property SproutLists_UserService $listUser
	 */
	public function init()
	{
		parent::init();

		$this->getAllListTypes();

		$this->listUser  = Craft::app()->getComponent('sproutLists_user');
		$this->listEmail = Craft::app()->getComponent('sproutLists_email');
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


	/**
	 * Retrieve id of "list" from lists table.
	 * @param  string $name Takes list converts to camel case,
	 *                      Queries to check if it exists.
	 *                      If not dynamically creates it.
	 * @return int          Returns id of existing or dynamic list.
	 */
	public function getListId($name)
	{
		$handle = $this->camelCase($name);

		$listId = craft()->db->createCommand()
			->select('id')
			->from('sproutlists_lists')
			->where(array(
				'AND',
				'name = :name',
				'handle = :handle'
			), array(
				':name' => $name,
				':handle' => $handle
			))->queryScalar();

		// If no key found dynamically create one
		if(!$listId)
		{
			$record = new SproutLists_ListsRecord;
			$record->name = $name;
			$record->handle = $handle;

			$record->save();

			return $record->id;
		}

		return $listId;
	}

	public function prepareIdsForQuery($ids)
	{
		if (!is_array($ids))
		{
			return ArrayHelper::stringToArray($ids);
		}

		return $ids;
	}

	public function getAllLists()
	{
		$records = SproutLists_ListsRecord::model()->findAll();

		$lists = array();

		if (!empty($records))
		{
			$lists = SproutLists_ListsModel::populateModels($records);
		}

		return $lists;
	}

	public function getListById($id)
	{
		$record = SproutLists_ListsRecord::model()->findById($id);

		$list = new SproutLists_ListsModel;

		if (!empty($record))
		{
			$list = SproutLists_ListsModel::populateModel($record);
		}

		return $list;
	}

	/**
	 * Returns camelCased version of original string.
	 * @param  string $str     String to camel case.
	 * @param  array  $noStrip Characters to strip (optional).
	 * @return string          Camel cased string.
	 */
	public static function camelCase($str, array $noStrip = [])
	{
		// non-alpha and non-numeric characters become spaces
		$str = preg_replace('/[^a-z0-9' . implode("", $noStrip) . ']+/i', ' ', $str);
		$str = trim($str);

		// uppercase the first character of each word
		$str = ucwords($str);
		$str = str_replace(" ", "", $str);
		$str = lcfirst($str);

		return $str;
	}
}
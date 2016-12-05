<?php

namespace Craft;

class SproutListsService extends BaseApplicationComponent
{
	protected $listTypes = array();

	public $listUser;
	public $listRecipient;


	public function init()
	{
		parent::init();

		$this->getAllListTypes();

		$this->listRecipient = Craft::app()->getComponent('sproutLists_recipient');
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
				'OR',
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

	public function getLists()
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

	public function saveList(SproutLists_ListsModel $model)
	{
		$result = false;

		if ($model->id)
		{
			$record = SproutLists_ListsRecord::model()->findById($model->id);
		}
		else
		{
			$record = new SproutLists_ListsRecord();
		}

		$modelAttributes = $model->getAttributes();

		if (!empty($modelAttributes))
		{
			foreach ($modelAttributes as $handle => $value)
			{
				$record->setAttribute($handle, $value);
			}
		}

		$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;

		if ($record->validate())
		{
			if ($record->save(false))
			{
				$model->id = $record->id;

				if ($transaction && $transaction->active)
				{
					$transaction->commit();
				}

				$result = true;
			}
		}
		else
		{
			$model->addErrors($record->getErrors());
		}

		return $result;
	}

	public function deleteList($id)
	{
		$record = SproutLists_ListsRecord::model()->findById($id);

		if ($record != null)
		{
			if ($record->delete())
			{
				$lists = SproutLists_ListsRecipientsRecord::model()->findByAttributes(array('listId' => $id));

				if ($lists != null)
				{
					return SproutLists_ListsRecipientsRecord::model()->deleteAll('listId = :listId', array(':listId' => $id));
				}
				return true;
			}
		}

		return false;
	}

	public function getListsHtml($elementId = null, $type = 'email')
	{
		$values = array();

		if ($elementId != null)
		{
			$listElementAttributes = array(
				'elementId' => $elementId,
				'type'      => $type
			);

			$listRecipients = SproutLists_ListsElementsRelationsRecord::model()->findAllByAttributes($listElementAttributes);

			if ($listRecipients != null)
			{
				foreach ($listRecipients as $listRecipient)
				{
					$values[] = $listRecipient->listId;
				}
			}
		}

		return $this->getRecipientListsHtml($values);
	}

	/**
	 * @param null $element
	 *
	 * @return \Twig_Markup
	 */
	public function getRecipientListsHtml($values = array(), $default = array())
	{
		$lists   = $this->getLists();
		$options = array();

		if (count($lists))
		{
			foreach ($lists as $list)
			{
				$options[] = array(
					'label' => sprintf('%s', $list->name),
					'value' => $list->id
				);
			}
		}

		if (!empty($default))
		{
			$values = $default;
		}

		// @todo - Move template code to the template
		$checkboxGroup = craft()->templates->renderMacro(
			'_includes/forms', 'checkboxGroup', array(
				array(
					'name'    => 'sproutlists[recipientLists]',
					'options' => $options,
					'values'  => $values
				)
			)
		);

		$html = craft()->templates->renderMacro(
			'_includes/forms', 'field', array(
				array(
					'id'     => 'recipientLists'
				),
				$checkboxGroup
			)
		);

		return TemplateHelper::getRaw($html);
	}

	public function getListsByRecipientId($id)
	{
		$lists         = array();
		$record        = SproutLists_ListsRecipientsRecord::model();
		$relationships = $record->findAllByAttributes(array('recipientId' => $id));

		if (count($relationships))
		{
			foreach ($relationships as $relationship)
			{
				$lists[] = sproutLists()->getListById($relationship->listId);
			}
		}

		return $lists;
	}

	public function addSyncElement(array $recipientLists, $elementId, $type = 'email')
	{
		$subscription = array(
			'elementId' => $elementId,
			'type'      => $type
		);

		$listRecipients = SproutLists_ListsElementsRelationsRecord::model()->findAllByAttributes($subscription);

		if ($listRecipients != null)
		{
			SproutLists_ListsElementsRelationsRecord::model()->deleteAllByAttributes($subscription);
		}

		return $this->addListsElement($recipientLists, $elementId, $type);
	}

	public function addListsElement(array $recipientLists, $elementId, $type = 'email')
	{
		$subscription = array(
			'elementId' => $elementId,
			'type'      => $type
		);

		$listRecordIds = $recipientLists['recipientLists'];

		$subscriptionModel = SproutLists_SubscriptionModel::populateModel($subscription);

		return $this->saveListsElement($listRecordIds, $subscriptionModel);
	}

	public function saveListsElement($listRecordIds, $subscriptionModel)
	{
		$result = false;

		if (!empty($listRecordIds))
		{
			foreach ($listRecordIds as $listRecordId)
			{
				$record = new SproutLists_ListsElementsRelationsRecord;

				$subscription = array(
					'elementId' => $subscriptionModel->elementId,
					'type'      => $subscriptionModel->type,
					'listId'    => $listRecordId
				);

				$listRecipients = SproutLists_ListsElementsRelationsRecord::model()->findAllByAttributes($subscription);

				// to avoid duplication
				if ($listRecipients != null) return;

				$record->elementId = $subscriptionModel->elementId;
				$record->type      = $subscriptionModel->type;
				$record->listId     = $listRecordId;

				$result = $record->save(false);
			}
		}

		return $result;
	}

	public function getAllRecipientsByElementIds($elementIds)
	{
		$subscription = array(
			'elementId' => $elementIds,
		);

		$lists = SproutLists_ListsElementsRelationsRecord::model()->findAllByAttributes($subscription);

		$listIds = array();

		if ($lists != null)
		{
			foreach ($lists as $list)
			{
				$listIds[] = $list->listId;
			}
		}

		return $this->getAllRecipientsByListIds($listIds);
	}

	public function getAllRecipientsByListIds(array $listIds)
	{
		$attributes = array(
			'id' => $listIds
		);

		$records = SproutLists_ListsRecord::model()->findAllByAttributes($attributes);

		$recipientLists = array();

		if (!empty($records))
		{
			foreach ($records as $record)
			{
				$recipientLists = array_merge($recipientLists, $record->recipients);
			}
		}

		$recipients = array();

		if (!empty($recipientLists))
		{
			foreach ($recipientLists as $recipientList)
			{
				$recipients[] = $recipientList;
			}
		}

		return $recipients;
	}

	public function getElementTitle($elementId)
	{
		$result = '';

		$element = craft()->elements->getElementById($elementId);

		if ($element != null)
		{
			$result = $element->id;

			if ($element->title != null)
			{
				$result = $element->id . " : " . $element->title;
			}
		}

		return $result;
	}

	public function getListElements()
	{
		$results = array();

		$elements = SproutLists_ListsElementsRelationsRecord::model()->findAll();

		if ($elements != null)
		{
			$results = $elements;
		}

		return $results;
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
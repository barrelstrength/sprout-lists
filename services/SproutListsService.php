<?php

namespace Craft;

class SproutListsService extends BaseApplicationComponent
{
	protected $listTypes = array();

	public $subscribers;

	public function init()
	{
		parent::init();

		$this->getAllListTypes();

		$this->subscribers = Craft::app()->getComponent('sproutLists_subscriber');
	}

	public function getAllListTypes()
	{
		$registeredListTypes = craft()->plugins->call('registerSproutListsListType');

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

		$listId = null;

		$list = SproutLists_ListRecord::model()->findByAttributes(array('handle' => $handle));

		// If no key found dynamically create one
		if ($list == null)
		{
			$model = new SproutLists_ListModel;
			$model->name = $name;
			$model->handle = $handle;

			$this->saveList($model);

			$listId = $model->id;
		}
		else
		{
			$listId = $list->id;
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
		$records = SproutLists_ListRecord::model()->findAll();

		$lists = array();

		if (!empty($records))
		{
			$lists = SproutLists_ListModel::populateModels($records);
		}

		return $lists;
	}

	public function getListById($id)
	{
		$record = SproutLists_ListRecord::model()->findById($id);

		$list = new SproutLists_ListModel;

		if (!empty($record))
		{
			$list = SproutLists_ListModel::populateModel($record);
		}

		return $list;
	}

	public function saveList(SproutLists_ListModel $model)
	{
		$result = false;

		if ($model->id)
		{
			$record = SproutLists_ListRecord::model()->findById($model->id);
		}
		else
		{
			$record = new SproutLists_ListRecord();
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
			try
			{
				if (craft()->elements->saveElement($model))
				{
					$record->id = $model->id;

					if ($record->save(false))
					{
						if ($transaction && $transaction->active)
						{
							$transaction->commit();
						}

						$result = true;
					}
				}
			}
			catch (\Exception $e)
			{
				if ($transaction && $transaction->active)
				{
					$transaction->rollback();
				}

				throw $e;
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
		$record = SproutLists_ListRecord::model()->findById($id);

		if ($record != null)
		{
			if ($record->delete())
			{
				$lists = SproutLists_ListsSubscribersRecord::model()->findByAttributes(array('listId' => $id));

				if ($lists != null)
				{
					return SproutLists_ListsSubscribersRecord::model()->deleteAll('listId = :listId', array(':listId' => $id));
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

			$listSubscribers = SproutLists_SubscriptionsRecord::model()->findAllByAttributes($listElementAttributes);

			if ($listSubscribers != null)
			{
				foreach ($listSubscribers as $listSubscriber)
				{
					$values[] = $listSubscriber->listId;
				}
			}
		}

		return $this->getSubscriberListsHtml($values);
	}

	/**
	 * @param null $element
	 *
	 * @return \Twig_Markup
	 */
	public function getSubscriberListsHtml($values = array(), $default = array())
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
					'name'    => 'sproutlists[subscriberLists]',
					'options' => $options,
					'values'  => $values
				)
			)
		);

		$html = craft()->templates->renderMacro(
			'_includes/forms', 'field', array(
				array(
					'id'     => 'subscriberLists'
				),
				$checkboxGroup
			)
		);

		return TemplateHelper::getRaw($html);
	}

	public function getListsBySubscriberId($id)
	{
		$lists         = array();
		$record        = SproutLists_ListsSubscribersRecord::model();
		$relationships = $record->findAllByAttributes(array('subscriberId' => $id));

		if (count($relationships))
		{
			foreach ($relationships as $relationship)
			{
				$lists[] = sproutLists()->getListById($relationship->listId);
			}
		}

		return $lists;
	}

	public function addSyncElement(array $subscriberLists, $elementId, $type = 'email')
	{
		$subscription = array(
			'elementId' => $elementId,
			'type'      => $type
		);

		$listSubscribers = SproutLists_SubscriptionsRecord::model()->findAllByAttributes($subscription);

		if ($listSubscribers != null)
		{
			SproutLists_SubscriptionsRecord::model()->deleteAllByAttributes($subscription);
		}

		return $this->addListsElement($subscriberLists, $elementId, $type);
	}

	public function addListsElement(array $subscriberLists, $elementId, $type = 'email')
	{
		$subscription = array(
			'elementId' => $elementId,
			'type'      => $type
		);

		$listRecordIds = $subscriberLists['subscriberLists'];

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
				$record = new SproutLists_SubscriptionsRecord;

				$subscription = array(
					'elementId' => $subscriptionModel->elementId,
					'type'      => $subscriptionModel->type,
					'listId'    => $listRecordId
				);

				$listSubscribers = SproutLists_SubscriptionsRecord::model()->findAllByAttributes($subscription);

				// to avoid duplication
				if ($listSubscribers != null) return;

				$record->elementId = $subscriptionModel->elementId;
				$record->type      = $subscriptionModel->type;
				$record->listId     = $listRecordId;

				$result = $record->save(false);
			}
		}

		return $result;
	}

	/**
	 * @todo - this function doesn't appear to be in use
	 *
	 * @param $elementIds
	 *
	 * @return array
	 */
	public function getAllSubscribersByElementIds($elementIds)
	{
		$subscription = array(
			'elementId' => $elementIds,
		);

		$lists = SproutLists_SubscriptionsRecord::model()->findAllByAttributes($subscription);

		$listIds = array();

		if ($lists != null)
		{
			foreach ($lists as $list)
			{
				$listIds[] = $list->listId;
			}
		}

		return $this->getAllSubscribersByListIds($listIds);
	}

	public function getAllSubscribersByListIds(array $listIds)
	{
		$attributes = array(
			'id' => $listIds
		);

		$records = SproutLists_ListRecord::model()->findAllByAttributes($attributes);

		$subscriberLists = array();

		if (!empty($records))
		{
			foreach ($records as $record)
			{
				$subscriberLists = array_merge($subscriberLists, $record->subscribers);
			}
		}

		$subscribers = array();

		if (!empty($subscriberLists))
		{
			foreach ($subscriberLists as $subscriberList)
			{
				$subscribers[] = $subscriberList;
			}
		}

		return $subscribers;
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

		$elements = SproutLists_SubscriptionsRecord::model()->findAll();

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
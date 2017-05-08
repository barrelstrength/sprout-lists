<?php
namespace Craft;

class SproutLists_ListsService extends BaseApplicationComponent
{
	protected $listTypes = array();

	public function init()
	{
		parent::init();

		$this->getAllListTypes();
	}

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

	public function saveListElement($listRecordIds, $subscriptionModel)
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
				if ($listSubscribers != null)
				{
					return;
				}

				$record->elementId = $subscriptionModel->elementId;
				$record->type      = $subscriptionModel->type;
				$record->listId    = $listRecordId;

				$result = $record->save(false);
			}
		}

		return $result;
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
				$lists = SproutLists_SubscriptionsRecord::model()->findByAttributes(array('listId' => $id));

				if ($lists != null)
				{
					return SproutLists_SubscriptionsRecord::model()->deleteAll('listId = :listId', array(':listId' => $id));
				}

				return true;
			}
		}

		return false;
	}

	/**
	 * Retrieve id of "list" from lists table.
	 * Queries to check if it exists.
	 * If not dynamically creates it.
	 *
	 * @param  string $handle
	 *
	 * @return int          Returns id of existing or dynamic list.
	 */
	public function getListByHandle($listHandle, SproutLists_SubscriptionModel $subscription = null)
	{
		$list = SproutLists_ListRecord::model()->findByAttributes(array('handle' => $listHandle));

		// If no List exists, dynamically create one
		if ($list == null)
		{
			$list            = new SproutLists_ListModel();
			$list->name      = $subscription->list;
			$list->handle    = $subscription->list;
			$list->elementId = $subscription->elementId != null ? $subscription->elementId : null;
			$list->type      = $subscription->type != null ? $subscription->type : 'subscriber';

			$this->saveList($list);
		}

		return $list;
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

	public function getListsBySubscriberId($id)
	{
		$lists         = array();
		$record        = SproutLists_SubscriptionsRecord::model();
		$relationships = $record->findAllByAttributes(array('subscriberId' => $id));

		if (count($relationships))
		{
			foreach ($relationships as $relationship)
			{
				$lists[] = sproutLists()->lists->getListById($relationship->listId);
			}
		}

		return $lists;
	}

	/**
	 * Returns a new List Type Class for the given List Type
	 *
	 * @param $type
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function getListType($type)
	{
		$type = !is_null($type) ? $type : 'subscriber';
		$type = ucwords($type);

		$className = 'SproutLists_' . $type . 'ListType';

		$namespace = 'Craft\\' . $className;

		if (!isset($this->listTypes[$className]))
		{
			throw new \Exception('Invalid List Type.');
		}

		return new $namespace;
	}

	/**
	 * @return array
	 */
	public function getListsWithSubscribers()
	{
		$records = SproutLists_SubscriberRecord::model()->with('subscriberLists')->findAll();
		$ids     = array();
		$lists   = array();

		if ($records)
		{
			foreach ($records as $record)
			{
				$ids[] = $record->id;
			}

			$query = craft()->db->createCommand()
				->select('listId')
				->where(array('in', 'subscriberId', $ids))
				->from('sproutlists_subscriptions')
				->group('listId');

			$results = $query->queryAll();

			if (!empty($results))
			{
				foreach ($results as $result)
				{
					$lists[] = sproutLists()->lists->getListById($result['listId']);
				}
			}
		}

		return $lists;
	}

	/**
	 * @param array $values
	 * @param array $default
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

		// @todo - Move template code to a template
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
					'id' => 'subscriberLists'
				),
				$checkboxGroup
			)
		);

		return TemplateHelper::getRaw($html);
	}

	/**
	 * Returns camelCased version of original string.
	 *
	 * @param  string $str     String to camel case.
	 * @param  array  $noStrip Characters to strip (optional).
	 *
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
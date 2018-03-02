<?php

namespace barrelstrength\sproutlists\elements;

use barrelstrength\sproutlists\elements\actions\DeleteList;
use barrelstrength\sproutlists\elements\db\ListsQuery;
use craft\base\Element;
use Craft;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\UrlHelper;
use barrelstrength\sproutlists\records\Lists as ListsRecord;
use yii\web\ErrorHandler;

class Lists extends Element
{
    public $id;
    public $elementId;
    public $type;
    public $name;
    public $handle;
    public $totalSubscribers;

    public static function displayName(): string
    {
        return Craft::t('sprout-lists', 'Sprout Lists');
    }

    /**
     * @inheritdoc
     */
    public static function hasContent(): bool
    {
        return true;
    }


    /**
     * @inheritdoc
     */
    public static function hasStatuses(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getCpEditUrl()
    {
        return UrlHelper::cpUrl(
            'sprout-lists/lists/edit/'.$this->id
        );
    }

    public static function find(): ElementQueryInterface
    {
        return new ListsQuery(static::class);
    }

    protected static function defineSources(string $context = null): array
    {
        $sources = [
            [
                'key' => '*',
                'label' => Craft::t('sprout-lists', 'All Lists')
            ]
        ];

        return $sources;
    }

    /**
     * Use the name as the string representation.
     *
     * @return string
     */
    /** @noinspection PhpInconsistentReturnPointsInspection */
    public function __toString()
    {
        try {
            return $this->name;
        } catch (\Exception $e) {
            ErrorHandler::convertExceptionToError($e);
        }
    }


    /**
     * @inheritdoc
     */
    protected static function defineTableAttributes(): array
    {
        $attributes = [
            'name' => ['label' => Craft::t('sprout-lists', 'Name')],
            'handle' => ['label' => Craft::t('sprout-lists', 'List Handle')],
            'view' => ['label' => Craft::t('sprout-lists', 'View Subscribers')],
            'totalSubscribers' => ['label' => Craft::t('sprout-lists', 'Total Subscribers')],
            'dateCreated' => ['label' => Craft::t('sprout-lists', 'Date Created')],
            'dateUpdated' => ['label' => Craft::t('sprout-lists', 'Date Updated')]
        ];

        return $attributes;
    }

    /**
     * @param string $attribute
     *
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getTableAttributeHtml(string $attribute): string
    {
        $totalSubscribers = $this->totalSubscribers;

        switch ($attribute) {
            case "handle":

                return "<code>".$this->handle."</code>";

                break;

            case "view":

                if ($this->id && $totalSubscribers > 0) {
                    return "<a href='".UrlHelper::cpUrl('sprout-lists/subscribers/'.$this->handle)."' class='go'>".
                        Craft::t('sprout-lists', 'View Subscribers')."</a>";
                }
                return '';
                break;
        }

        return parent::getTableAttributeHtml($attribute);
    }

    public function getFieldLayout()
    {
        return Craft::$app->getFields()->getLayoutByType(static::class);
    }

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = [['name', 'handle'], 'required'];

        return $rules;
    }

    /**
     * @param bool $isNew
     *
     * @throws \Exception
     */
    public function afterSave(bool $isNew)
    {
        // Get the list record
        if (!$isNew) {
            $record = ListsRecord::findOne($this->id);

            if (!$record) {
                throw new \Exception('Invalid list ID: '.$this->id);
            }
        } else {
            $record = new ListsRecord();
            $record->id = $this->id;
            $record->elementId = $this->id;
        }

        $record->type = $this->type;
        $record->name = $this->name;
        $record->handle = $this->handle;

        $record->save(false);

        // Update the entry's descendants, who may be using this entry's URI in their own URIs
        Craft::$app->getElements()->updateElementSlugAndUri($this, true, true);

        parent::afterSave($isNew);
    }

    /**
     * @inheritdoc
     */
    protected static function defineActions(string $source = null): array
    {
        $actions = [];

        $actions[] = DeleteList::class;

        return $actions;
    }
}
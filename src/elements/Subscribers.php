<?php

namespace barrelstrength\sproutlists\elements;

use barrelstrength\sproutbase\contracts\sproutlists\SproutListsBaseListType;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutlists\elements\db\ListsQuery;
use barrelstrength\sproutlists\SproutLists;
use craft\base\Element;
use Craft;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\UrlHelper;
use barrelstrength\sproutlists\records\Subscribers as SubscribersRecord;

class Subscribers extends Element
{
    private $subscriberListsIds;

    public static function displayName(): string
    {
        return Craft::t('', 'Sprout Subscribers');
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
    public static function hasTitles(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasUris(): bool
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
            'sprout-lists/subscribers/edit/'.$this->id
        );
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
     * @inheritdoc
     */
    protected static function defineTableAttributes(): array
    {
        $attributes = [
            'name'   => ['label' => Craft::t('sprout-lists', 'Name')],
            'handle' => ['label' => Craft::t('sprout-lists', 'List Handle')],
            'view'   => ['label' => Craft::t('sprout-lists', 'View Subscribers')],
            'totalSubscribers' => ['label' => Craft::t('sprout-lists', 'Total Subscribers')],
            'dateCreated'      => ['label' => Craft::t('sprout-lists', 'Date Created')],
            'dateUpdated'      => ['label' => Craft::t('sprout-lists', 'Date Updated')]
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

        switch ($attribute)
        {
            case "handle":

                return "<code>" . $this->handle . "</code>";

                break;

            case "view":

                if ($this->id && $totalSubscribers > 0)
                {
                    return "<a href='" . UrlHelper::cpUrl('sprout-lists/subscribers/' . $this->handle) . "' class='go'>" . Craft::t('View Subscribers') . "</a>";
                }

                break;
        }

        return parent::getTableAttributeHtml($attribute);
    }

    /**
     * @return ElementQueryInterface
     */
    public static function find(): ElementQueryInterface
    {
        return new ListsQuery(static::class);
    }

    public function getFieldLayout()
    {
        return Craft::$app->getFields()->getLayoutByType(static::class);
    }

    /**
     * @param mixed|null $element
     *
     * @throws \Exception
     * @return array|string
     */
    public function getRecipients($element = null)
    {
        return SproutBase::$app->mailers->getRecipients($element, $this);
    }

    public function getUriFormat()
    {
        return "sprout-lists/{slug}";
    }

    /**
     * @return null|string
     * @throws \yii\base\Exception
     */
    public function getUrl()
    {
        if ($this->uri !== null) {
            return  UrlHelper::siteUrl($this->uri, null, null);
        }

        return null;
    }

    /**
     * @return array|mixed
     * @throws \HttpException
     */
    public function route()
    {
        // Only expose notification emails that have tokens and allow Live Preview requests
        if (!Craft::$app->request->getParam(Craft::$app->config->getGeneral()->tokenParam)
            && !Craft::$app->getRequest()->getIsLivePreview()) {
            throw new \HttpException(404);
        }
        $extension = null;

        if (($type = Craft::$app->request->get('type'))) {
            $extension = in_array(strtolower($type), ['txt', 'text']) ? '.txt' : null;
        }

        if (!Craft::$app->getView()->doesTemplateExist($this->template.$extension)) {
            $templateName = $this->template.$extension;

            SproutEmail::$app->utilities->addError(Craft::t('sprout-email', "The template '{templateName}' could not be found", [
                'templateName' => $templateName
            ]));
        }

        $event = SproutEmail::$app->notificationEmails->getEventById($this->eventId);

        $object = $event ? $event->getMockedParams() : null;

        return [
            'templates/render', [
                'template' => $this->template.$extension,
                'variables' => [
                    'email' => $this,
                    'object' => $object
                ]
            ]
        ];
    }

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = [['subjectLine', 'name'], 'required'];

        return $rules;
    }

    public function getListIds()
    {
        if (empty($this->subscriberListsIds))
        {
            $subscriberLists = $this->getListsBySubscriberId();

            if (count($subscriberLists))
            {
                foreach ($subscriberLists as $list)
                {
                    $this->subscriberListsIds[] = $list->id;
                }
            }
        }

        return $this->subscriberListsIds;
    }

    /**
     * Gets an array of SproutLists_ListModels to which this subscriber is subscribed.
     *
     * @return array
     */
    public function getListsBySubscriberId()
    {
        $lists    = array();

        $subscriptions = SubscribersRecord::find()->where([
            'subscriberId' => $this->id
        ])->all();

        $subscriberNamespace = 'barrelstrength\sproutlists\integrations\sproutlists\SubscriberListType';
        $listType = SproutLists::$app->lists->getListType($subscriberNamespace);

        if (count($subscriptions))
        {
            foreach ($subscriptions as $subscription)
            {
                /**
                 * @var $listType SproutListsBaseListType
                 */
                $lists[] = $listType->getListById($subscription->listId);
            }
        }

        return $lists;
    }
}
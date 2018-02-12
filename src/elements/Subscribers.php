<?php

namespace barrelstrength\sproutlists\elements;

use barrelstrength\sproutbase\contracts\sproutlists\SproutListsBaseListType;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutlists\elements\db\ListsQuery;
use barrelstrength\sproutlists\elements\db\SubscribersQuery;
use barrelstrength\sproutlists\SproutLists;
use craft\base\Element;
use Craft;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\UrlHelper;
use barrelstrength\sproutlists\records\Subscribers as SubscribersRecord;

class Subscribers extends Element
{
    public $id;
    public $email;
    public $firstName;
    public $lastName;
    public $userId;
    public $subscriberLists;
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
            'id'   => ['label' => Craft::t('sprout-lists', 'ID')],
            'email' => ['label' => Craft::t('sprout-lists', 'Email')],
            'firstName' => ['label' => Craft::t('sprout-lists', 'First Name')],
            'lastName' => ['label' => Craft::t('sprout-lists', 'Last Name')],
            'dateCreated'      => ['label' => Craft::t('sprout-lists', 'Date Created')],
            'dateUpdated'      => ['label' => Craft::t('sprout-lists', 'Date Updated')]
        ];

        return $attributes;
    }
    
    /**
     * @return ElementQueryInterface
     */
    public static function find(): ElementQueryInterface
    {
        return new SubscribersQuery(static::class);
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
     * @return mixed
     * @throws \Exception
     */
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
     * @return array
     * @throws \Exception
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

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = [['email'], 'required'];

        return $rules;
    }

    /**
     * @param bool $isNew
     *
     * @throws \Exception
     */
    public function afterSave(bool $isNew)
    {
        $plugin = Craft::$app->plugins->getPlugin('sprout-lists');

        if ($plugin)
        {
            $settings = $plugin->getSettings();
        }

        // Get the list record
        if (!$isNew) {
            $record = SubscribersRecord::findOne($this->id);

            if (!$record) {
                throw new \Exception('Invalid list ID: '.$this->id);
            }
        } else {
            $record = new SubscribersRecord();
            $record->id = $this->id;
        }

        $user = null;

        // Sync updates with Craft User if User Sync enabled
        if ($record->email && ($settings AND $settings->enableUserSync)) {
            $user = Craft::$app->users->getUserByUsernameOrEmail($record->email);

            if ($user != null)
            {
                $record->userId = $user->id;
            }
        }

        $record->userId    = $this->userId;
        $record->email     = $this->email;
        $record->firstName = $this->firstName;
        $record->lastName  = $this->lastName;

        $record->save(false);

        // Update the entry's descendants, who may be using this entry's URI in their own URIs
        Craft::$app->getElements()->updateElementSlugAndUri($this, true, true);

        parent::afterSave($isNew);
    }
}
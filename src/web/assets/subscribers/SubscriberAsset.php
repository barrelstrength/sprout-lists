<?php

namespace barrelstrength\sproutlists\web\assets\subscribers;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * Class EmailAsset
 *
 * @package barrelstrength\sproutemail\web\assets\email
 */
class SubscriberAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutlists/web/assets/subscribers/dist';

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/SubscriberElementIndex.js'
        ];

        parent::init();
    }
}
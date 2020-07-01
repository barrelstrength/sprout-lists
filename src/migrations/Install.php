<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutlists\migrations;

use barrelstrength\sproutlists\SproutLists;
use barrelstrength\sproutbase\SproutBase;
use craft\db\Migration;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\NotSupportedException;
use yii\web\ServerErrorHttpException;

class Install extends Migration
{
    /**
     * @throws ErrorException
     * @throws NotSupportedException
     * @throws ServerErrorHttpException
     * @throws Exception
     */
    public function safeUp()
    {
        SproutBase::$app->config->runInstallMigrations(SproutLists::getInstance());
    }

    public function safeDown()
    {
        SproutBase::$app->config->runUninstallMigrations(SproutLists::getInstance());
    }
}
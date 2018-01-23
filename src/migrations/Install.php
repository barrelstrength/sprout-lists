<?php

namespace barrelstrength\sproutlists\migrations;

use craft\db\Migration;
use barrelstrength\sproutbase\migrations\sproutemail\Install as SproutBaseNotificationInstall;

class Install extends Migration
{
    private $subscribersTable = '{{%sproutlists_subscribers}}';
    private $listsTable = '{{%sproutlists_lists}}';

    public function safeUp()
    {
        $this->createTable($this->listsTable,
            [
                'id' => $this->primaryKey(),
                'elementId'        => $this->integer()->notNull(),
                'type'             => $this->string(),
                'name'             => $this->string(),
                'handle'           => $this->string(),
                'totalSubscribers' => $this->integer(),
                'dateCreated'  => $this->dateTime()->notNull(),
                'dateUpdated'  => $this->dateTime()->notNull(),
                'uid'          => $this->uid()
            ]
        );

        $this->createTable($this->subscribersTable,
            [
                'id' => $this->primaryKey(),
                'userId'       => $this->integer()->notNull(),
                'email'        => $this->string(),
                'firstName'    => $this->string(),
                'lastName'     => $this->string(),
                'dateCreated'  => $this->dateTime()->notNull(),
                'dateUpdated'  => $this->dateTime()->notNull(),
                'uid'          => $this->uid()
            ]
        );
    }

    public function safeDown()
    {
        $this->dropTable($this->listsTable);
        $this->dropTable($this->subscribersTable);
    }
}
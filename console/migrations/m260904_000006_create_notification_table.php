<?php

declare(strict_types=1);

use yii\db\Migration;

class m260904_000006_create_notification_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%notification}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->null(),
            'title' => $this->string(255)->notNull(),
            'message' => $this->text()->notNull(),
            'type' => $this->string(20)->notNull()->defaultValue('info'),
            'is_read' => $this->boolean()->notNull()->defaultValue(false),
            'created_at' => $this->integer()->notNull(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        $this->createIndex('idx-notification-user_read', '{{%notification}}', ['user_id', 'is_read']);
        $this->createIndex('idx-notification-created_at', '{{%notification}}', 'created_at');
        $this->addForeignKey('fk-notification-user_id', '{{%notification}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk-notification-user_id', '{{%notification}}');
        $this->dropTable('{{%notification}}');
    }
}

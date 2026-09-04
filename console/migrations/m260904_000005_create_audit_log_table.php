<?php

declare(strict_types=1);

use yii\db\Migration;

class m260904_000005_create_audit_log_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%audit_log}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->null(),
            'action' => $this->string(80)->notNull(),
            'description' => $this->text()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'ip_address' => $this->string(45)->null(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        $this->createIndex('idx-audit_log-user_id', '{{%audit_log}}', 'user_id');
        $this->createIndex('idx-audit_log-created_at', '{{%audit_log}}', 'created_at');
        $this->addForeignKey('fk-audit_log-user_id', '{{%audit_log}}', 'user_id', '{{%user}}', 'id', 'SET NULL', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk-audit_log-user_id', '{{%audit_log}}');
        $this->dropTable('{{%audit_log}}');
    }
}

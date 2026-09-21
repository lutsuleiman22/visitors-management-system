<?php

declare(strict_types=1);

use yii\db\Migration;

class m260921_000005_create_reception_shifts_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%reception_shifts}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'branch_code' => $this->string(100)->notNull(),
            'started_at' => $this->dateTime()->notNull(),
            'stopped_at' => $this->dateTime()->null(),
            'status' => $this->smallInteger()->notNull()->defaultValue(1),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

        $this->execute('ALTER TABLE {{%reception_shifts}} ADD active_branch_code VARCHAR(100) GENERATED ALWAYS AS (IF(status = 1, branch_code, NULL)) STORED');
        $this->createIndex('idx-reception-shifts-open-branch', '{{%reception_shifts}}', 'active_branch_code', true);
        $this->createIndex('idx-reception-shifts-user', '{{%reception_shifts}}', 'user_id');
    }

    public function safeDown()
    {
        $this->dropTable('{{%reception_shifts}}');
    }
}
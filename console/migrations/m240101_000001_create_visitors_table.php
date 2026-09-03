<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Handles the creation of table `{{%visitors}}`.
 */
class m240101_000001_create_visitors_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';

        $this->createTable('{{%visitors}}', [
            'id' => $this->primaryKey(),
            'full_name' => $this->string()->notNull(),
            'phone_number' => $this->string()->notNull(),
            'national_id' => $this->string(),
            'photo_path' => $this->string(),
            'status' => $this->smallInteger()->notNull()->defaultValue(1),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $tableOptions);

        $this->createIndex('idx-visitors-national_id', '{{%visitors}}', 'national_id');
        $this->createIndex('idx-visitors-phone_number', '{{%visitors}}', 'phone_number');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%visitors}}');
    }
}

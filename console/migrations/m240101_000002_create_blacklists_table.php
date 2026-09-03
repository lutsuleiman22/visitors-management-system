<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Handles the creation of table `{{%blacklists}}`.
 */
class m240101_000002_create_blacklists_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';

        $this->createTable('{{%blacklists}}', [
            'id' => $this->primaryKey(),
            'national_id' => $this->string(),
            'phone_number' => $this->string(),
            'reason' => $this->text(),
            'status' => $this->smallInteger()->notNull()->defaultValue(1),
            'created_at' => $this->integer(),
        ], $tableOptions);

        $this->createIndex('idx-blacklists-national_id', '{{%blacklists}}', 'national_id');
        $this->createIndex('idx-blacklists-phone_number', '{{%blacklists}}', 'phone_number');
        $this->createIndex('idx-blacklists-status', '{{%blacklists}}', 'status');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%blacklists}}');
    }
}

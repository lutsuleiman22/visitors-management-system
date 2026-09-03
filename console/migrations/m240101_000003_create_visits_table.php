<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Handles the creation of table `{{%visits}}`.
 */
class m240101_000003_create_visits_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';

        $this->createTable('{{%visits}}', [
            'id' => $this->primaryKey(),
            'visitor_id' => $this->integer()->notNull(),
            'host_user_id' => $this->integer()->notNull(),
            'purpose' => $this->string(),
            'qr_code_hash' => $this->string(),
            'status' => $this->string()->notNull()->defaultValue('Checked-In'),
            'check_in_time' => $this->dateTime(),
            'check_out_time' => $this->dateTime(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $tableOptions);

        $this->createIndex('idx-visits-visitor_id', '{{%visits}}', 'visitor_id');
        $this->createIndex('idx-visits-host_user_id', '{{%visits}}', 'host_user_id');
        $this->createIndex('idx-visits-qr_code_hash', '{{%visits}}', 'qr_code_hash');
        $this->createIndex('idx-visits-status', '{{%visits}}', 'status');

        $this->addForeignKey(
            'fk-visits-visitor_id',
            '{{%visits}}',
            'visitor_id',
            '{{%visitors}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-visits-host_user_id',
            '{{%visits}}',
            'host_user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-visits-host_user_id', '{{%visits}}');
        $this->dropForeignKey('fk-visits-visitor_id', '{{%visits}}');
        $this->dropTable('{{%visits}}');
    }
}

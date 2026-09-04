<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%visitor}}`.
 */
class m260903_095006_add_columns_to_visitor_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%visitors}}', 'origin', $this->string()->null()->after('phone_number'));
        $this->addColumn('{{%visits}}', 'host_name', $this->string()->null()->after('host_user_id'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%visits}}', 'host_name');
        $this->dropColumn('{{%visitors}}', 'origin');
    }
}

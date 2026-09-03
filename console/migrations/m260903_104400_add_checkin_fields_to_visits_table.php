<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Adds check-in fields: origin, destination, pass number, and signature.
 * Makes host_user_id optional (self check-in no longer selects a host).
 */
class m260903_104400_add_checkin_fields_to_visits_table extends Migration
{
    public function safeUp()
    {
        $this->dropForeignKey('fk-visits-host_user_id', '{{%visits}}');

        $this->alterColumn('{{%visits}}', 'host_user_id', $this->integer()->null());

        $this->addForeignKey(
            'fk-visits-host_user_id',
            '{{%visits}}',
            'host_user_id',
            '{{%user}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addColumn('{{%visits}}', 'from_location', $this->string()->null()->after('purpose'));
        $this->addColumn('{{%visits}}', 'destination', $this->string()->null()->after('from_location'));
        $this->addColumn('{{%visits}}', 'visitor_pass_number', $this->string(32)->null()->after('destination'));
        $this->addColumn('{{%visits}}', 'signature_path', $this->string()->null()->after('visitor_pass_number'));

        $this->createIndex('uq-visits-visitor_pass_number', '{{%visits}}', 'visitor_pass_number', true);
    }

    public function safeDown()
    {
        $this->dropIndex('uq-visits-visitor_pass_number', '{{%visits}}');
        $this->dropColumn('{{%visits}}', 'signature_path');
        $this->dropColumn('{{%visits}}', 'visitor_pass_number');
        $this->dropColumn('{{%visits}}', 'destination');
        $this->dropColumn('{{%visits}}', 'from_location');

        $this->dropForeignKey('fk-visits-host_user_id', '{{%visits}}');
        $this->alterColumn('{{%visits}}', 'host_user_id', $this->integer()->notNull());
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
}

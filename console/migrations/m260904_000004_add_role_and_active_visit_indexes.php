<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Adds indexes used by role checks and active-visit monitoring.
 */
class m260904_000004_add_role_and_active_visit_indexes extends Migration
{
    public function safeUp(): void
    {
        $this->createIndex('idx-user-role', '{{%user}}', 'role');
        $this->createIndex(
            'idx-visits-status-check_out_time',
            '{{%visits}}',
            ['status', 'check_out_time'],
        );
    }

    public function safeDown(): void
    {
        $this->dropIndex('idx-visits-status-check_out_time', '{{%visits}}');
        $this->dropIndex('idx-user-role', '{{%user}}');
    }
}

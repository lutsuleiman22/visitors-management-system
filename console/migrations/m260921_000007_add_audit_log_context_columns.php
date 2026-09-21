<?php

declare(strict_types=1);

use yii\db\Migration;

class m260921_000007_add_audit_log_context_columns extends Migration
{
    public function safeUp(): void
    {
        $schema = $this->db->getTableSchema('{{%audit_log}}', true);
        if ($schema !== null && !isset($schema->columns['model'])) {
            $this->addColumn('{{%audit_log}}', 'model', $this->string(80)->null());
        }
        if ($schema !== null && !isset($schema->columns['record_id'])) {
            $this->addColumn('{{%audit_log}}', 'record_id', $this->integer()->null());
        }
    }

    public function safeDown(): void
    {
        $schema = $this->db->getTableSchema('{{%audit_log}}', true);
        if ($schema !== null && isset($schema->columns['record_id'])) {
            $this->dropColumn('{{%audit_log}}', 'record_id');
        }
        if ($schema !== null && isset($schema->columns['model'])) {
            $this->dropColumn('{{%audit_log}}', 'model');
        }
    }
}

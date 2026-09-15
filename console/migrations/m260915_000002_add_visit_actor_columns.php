<?php

declare(strict_types=1);

use yii\db\Migration;

class m260915_000002_add_visit_actor_columns extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%visits}}', 'checked_in_by_user_id', $this->integer()->null()->after('branch_code'));
        $this->addColumn('{{%visits}}', 'checked_out_by_user_id', $this->integer()->null()->after('checked_in_by_user_id'));
        $this->createIndex('idx-visits-checked_in_by', '{{%visits}}', 'checked_in_by_user_id');
        $this->createIndex('idx-visits-checked_out_by', '{{%visits}}', 'checked_out_by_user_id');
    }

    public function safeDown(): void
    {
        $this->dropIndex('idx-visits-checked_out_by', '{{%visits}}');
        $this->dropIndex('idx-visits-checked_in_by', '{{%visits}}');
        $this->dropColumn('{{%visits}}', 'checked_out_by_user_id');
        $this->dropColumn('{{%visits}}', 'checked_in_by_user_id');
    }
}

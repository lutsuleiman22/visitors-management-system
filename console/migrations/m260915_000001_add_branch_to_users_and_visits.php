<?php

declare(strict_types=1);

use yii\db\Migration;

class m260915_000001_add_branch_to_users_and_visits extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%user}}', 'branch_code', $this->string(64)->null()->after('role'));
        $this->addColumn('{{%visits}}', 'branch_code', $this->string(64)->null()->after('host_user_id'));
        $this->createIndex('idx-user-branch_code', '{{%user}}', 'branch_code');
        $this->createIndex('idx-visits-branch_code', '{{%visits}}', 'branch_code');
    }

    public function safeDown(): void
    {
        $this->dropIndex('idx-visits-branch_code', '{{%visits}}');
        $this->dropIndex('idx-user-branch_code', '{{%user}}');
        $this->dropColumn('{{%visits}}', 'branch_code');
        $this->dropColumn('{{%user}}', 'branch_code');
    }
}

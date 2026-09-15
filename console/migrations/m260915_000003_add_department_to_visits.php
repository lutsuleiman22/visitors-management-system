<?php

declare(strict_types=1);

use yii\db\Migration;

class m260915_000003_add_department_to_visits extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%visits}}', 'department_code', $this->string(100)->null()->after('branch_code'));
        $this->createIndex('idx-visits-department_code', '{{%visits}}', 'department_code');
    }

    public function safeDown(): void
    {
        $this->dropIndex('idx-visits-department_code', '{{%visits}}');
        $this->dropColumn('{{%visits}}', 'department_code');
    }
}

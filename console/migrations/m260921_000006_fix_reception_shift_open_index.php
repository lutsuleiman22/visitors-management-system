<?php

declare(strict_types=1);

use yii\db\Migration;

class m260921_000006_fix_reception_shift_open_index extends Migration
{
    public function safeUp()
    {
        $this->dropIndex('idx-reception-shifts-open-branch', '{{%reception_shifts}}');
        $this->execute('ALTER TABLE {{%reception_shifts}} ADD active_branch_code VARCHAR(100) GENERATED ALWAYS AS (IF(status = 1, branch_code, NULL)) STORED');
        $this->createIndex('idx-reception-shifts-open-branch', '{{%reception_shifts}}', 'active_branch_code', true);
    }

    public function safeDown()
    {
        $this->dropIndex('idx-reception-shifts-open-branch', '{{%reception_shifts}}');
        $this->dropColumn('{{%reception_shifts}}', 'active_branch_code');
        $this->createIndex('idx-reception-shifts-open-branch', '{{%reception_shifts}}', ['branch_code', 'status'], true);
    }
}
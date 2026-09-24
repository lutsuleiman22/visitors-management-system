<?php

declare(strict_types=1);

use yii\db\Migration;

class m260924_000007_add_timetable_to_reception_shifts extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%reception_shifts}}', 'timetable', $this->string(100)->null()->defaultValue('Morning Shift'));
    }

    public function safeDown(): void
    {
        $this->dropColumn('{{%reception_shifts}}', 'timetable');
    }
}

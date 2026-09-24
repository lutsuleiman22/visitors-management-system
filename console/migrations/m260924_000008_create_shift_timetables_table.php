<?php

declare(strict_types=1);

use yii\db\Migration;

class m260924_000008_create_shift_timetables_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%shift_timetables}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(100)->notNull(),
            'code' => $this->string(50)->notNull()->unique(),
            'start_time' => $this->string(20)->notNull()->defaultValue('08:00:00'),
            'end_time' => $this->string(20)->notNull()->defaultValue('16:00:00'),
            'status' => $this->tinyInteger()->notNull()->defaultValue(1),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
        ]);

        $this->batchInsert('{{%shift_timetables}}', ['name', 'code', 'start_time', 'end_time', 'status', 'created_at', 'updated_at'], [
            ['Morning Shift', 'morning', '08:00:00', '16:00:00', 1, date('Y-m-d H:i:s'), date('Y-m-d H:i:s')],
            ['Afternoon Shift', 'afternoon', '12:00:00', '20:00:00', 1, date('Y-m-d H:i:s'), date('Y-m-d H:i:s')],
            ['Evening Shift', 'evening', '16:00:00', '00:00:00', 1, date('Y-m-d H:i:s'), date('Y-m-d H:i:s')],
        ]);
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%shift_timetables}}');
    }
}

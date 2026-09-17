<?php

declare(strict_types=1);

use yii\db\Migration;

class m260917_000001_add_gender_to_visitors extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%visitors}}', 'gender', $this->string(20)->null()->after('phone_number'));
    }

    public function safeDown(): void
    {
        $this->dropColumn('{{%visitors}}', 'gender');
    }
}

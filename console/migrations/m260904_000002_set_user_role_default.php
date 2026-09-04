<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Ensures new users default to the least-privileged application role.
 */
class m260904_000002_set_user_role_default extends Migration
{
    public function safeUp(): void
    {
        $this->alterColumn('{{%user}}', 'role', $this->string(20)->notNull()->defaultValue('user'));
    }

    public function safeDown(): void
    {
        $this->alterColumn('{{%user}}', 'role', $this->string(20)->notNull()->defaultValue('user'));
    }
}

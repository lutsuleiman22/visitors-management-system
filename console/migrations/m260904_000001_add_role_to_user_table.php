<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Adds persistent roles to backend users.
 */
class m260904_000001_add_role_to_user_table extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn(
            '{{%user}}',
            'role',
            $this->string(20)->notNull()->defaultValue('user')->after('email'),
        );
    }

    public function safeDown(): void
    {
        $this->dropColumn('{{%user}}', 'role');
    }
}

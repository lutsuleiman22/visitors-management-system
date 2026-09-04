<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Normalizes legacy or invalid user roles to the least-privileged role.
 */
class m260904_000003_normalize_user_roles extends Migration
{
    public function safeUp(): void
    {
        $this->update(
            '{{%user}}',
            ['role' => 'user'],
            ['not in', 'role', ['admin', 'reception', 'security', 'user']],
        );
    }

    public function safeDown(): void
    {
        // Invalid legacy role values cannot be restored safely.
    }
}

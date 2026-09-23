<?php

declare(strict_types=1);

namespace common\tests\Unit\Models;

use Codeception\Test\Unit;
use common\models\User;

final class UserTest extends Unit
{
    public function testCreatableRoleList(): void
    {
        verify(User::creatableRoleList())
            ->equals([
                User::ROLE_RECEPTION => 'Reception',
                User::ROLE_SECURITY => 'Security',
            ]);
    }
}

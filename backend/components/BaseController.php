<?php

declare(strict_types=1);

namespace backend\components;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

abstract class BaseController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    protected function requireRole(string ...$roles): void
    {
        $identity = Yii::$app->user->identity;
        $role = $identity === null ? '' : (string) $identity->role;

        if (!in_array($role, $roles, true)) {
            throw new ForbiddenHttpException('You are not authorized to perform this action.');
        }
    }
}

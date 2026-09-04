<?php

declare(strict_types=1);

namespace common\services;

use common\models\AuditLog;
use Yii;

final class AuditLogService
{
    public static function logAction(string $action, string $description, ?int $userId = null): void
    {
        $log = new AuditLog([
            'user_id' => $userId ?? (Yii::$app->user->isGuest ? null : (int) Yii::$app->user->id),
            'action' => $action,
            'description' => $description,
            'created_at' => date('Y-m-d H:i:s'),
            'ip_address' => Yii::$app->request->userIP,
        ]);
        try {
            if (!$log->save()) {
                Yii::error($log->getErrors(), __METHOD__);
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
        }
    }
}

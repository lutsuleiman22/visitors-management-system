<?php

declare(strict_types=1);

namespace common\components;

use common\models\AuditLog;
use Yii;

final class AuditLogger
{
    public static function log($action, $model = null, $recordId = null, $description = null): void
    {
        $table = AuditLog::tableName();
        $columns = AuditLog::getTableSchema()?->columns ?? [];

        $attributes = [
            'user_id' => Yii::$app->user->isGuest ? null : (int) Yii::$app->user->id,
            'action' => strtoupper((string) $action),
            'description' => (string) ($description ?? 'System activity'),
            'created_at' => date('Y-m-d H:i:s'),
            'ip_address' => Yii::$app->request->userIP ?? '',
        ];

        if (isset($columns['model'])) {
            $attributes['model'] = $model !== null ? (string) $model : null;
        }

        if (isset($columns['record_id'])) {
            $attributes['record_id'] = $recordId !== null ? (int) $recordId : null;
        }

        try {
            $log = new AuditLog($attributes);
            if (!$log->save()) {
                Yii::error($log->getErrors(), __METHOD__);
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
        }
    }
}

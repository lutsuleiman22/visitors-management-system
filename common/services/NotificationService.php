<?php

declare(strict_types=1);

namespace common\services;

use common\models\Notification;
use common\models\Visit;
use Yii;

final class NotificationService
{
    public static function createNotification(string $message, string $type = Notification::TYPE_INFO, ?int $userId = null, string $title = 'Visitor Management'): void
    {
        $notification = new Notification([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'created_at' => time(),
        ]);
        try {
            if (!$notification->save()) {
                Yii::error($notification->getErrors(), __METHOD__);
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
        }
    }

    public static function unreadCount(?int $userId): int
    {
        if ($userId === null) {
            return 0;
        }
        try {
            return (int) Notification::find()->where(['user_id' => $userId, 'is_read' => 0])->count();
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return 0;
        }
    }

    public static function notifyOverdue(int $hours = 8): void
    {
        $cutoff = date('Y-m-d H:i:s', time() - ($hours * 3600));
        try {
            $overdue = Visit::find()->with('visitor')->where(['status' => Visit::STATUS_CHECKED_IN, 'check_out_time' => null])->andWhere(['<', 'check_in_time', $cutoff])->all();
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return;
        }
        foreach ($overdue as $visit) {
            try {
                $exists = Notification::find()
                    ->where(['like', 'message', 'visit #' . $visit->id . ' overdue'])
                    ->andWhere(['>=', 'created_at', strtotime('today')])
                    ->exists();
            } catch (\Throwable $exception) {
                Yii::error($exception->getMessage(), __METHOD__);
                continue;
            }
            if (!$exists) {
                self::createNotification('Visit #' . $visit->id . ' overdue: ' . ($visit->visitor->full_name ?? 'Unknown'), Notification::TYPE_WARNING);
            }
        }
    }
}

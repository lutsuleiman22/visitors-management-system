<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\BaseController;
use common\models\User;
use common\models\Visit;
use common\services\NotificationService;
use Yii;
use yii\web\Response;

class DashboardController extends BaseController
{
    public function actionAnalytics(): string
    {
        $this->requireRole(User::ROLE_ADMIN);
        $today = date('Y-m-d 00:00:00');
        NotificationService::notifyOverdue();
        return $this->render('analytics', [
            'today' => (int) Visit::find()->where(['>=', 'check_in_time', $today])->count(),
            'inside' => (int) Visit::find()->where(['status' => Visit::STATUS_CHECKED_IN, 'check_out_time' => null])->count(),
            'checkedOut' => (int) Visit::find()->where(['status' => Visit::STATUS_CHECKED_OUT])->count(),
            'pending' => (int) Visit::find()->where(['status' => 'Pending'])->count(),
        ]);
    }

    public function actionChartData(): Response
    {
        $this->requireRole(User::ROLE_ADMIN);
        $from = date('Y-m-d', strtotime('-6 days')) . ' 00:00:00';
        try {
            $daily = Visit::find()->select(['day' => 'DATE(check_in_time)', 'count' => 'COUNT(*)'])->where(['>=', 'check_in_time', $from])->groupBy(['day'])->asArray()->all();
            $roles = User::find()->select(['role', 'count' => 'COUNT(*)'])->groupBy(['role'])->asArray()->all();
            $hours = Visit::find()->select(['hour' => 'HOUR(check_in_time)', 'count' => 'COUNT(*)'])->where(['>=', 'check_in_time', $from])->groupBy(['hour'])->asArray()->all();
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->asJson(['success' => false, 'data' => [], 'message' => 'Analytics data is temporarily unavailable.']);
        }
        return $this->asJson(['success' => true, 'data' => ['daily' => $daily, 'roles' => $roles, 'hours' => $hours], 'message' => '']);
    }
}

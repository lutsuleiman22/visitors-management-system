<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\BaseController;
use common\models\AuditLog;
use common\models\User;
use common\models\Visit;
use common\models\Visitor;
use Yii;
use yii\web\Response;

class AdminController extends BaseController
{
    public function actionDashboard(): string
    {
        $this->requireRole(User::ROLE_ADMIN);

        try {
            $data = [
                'totalUsers' => (int) User::find()->count(),
                'totalVisitors' => (int) Visitor::find()->count(),
                'totalVisits' => (int) Visit::find()->count(),
                'activeVisits' => (int) Visit::find()->where(['status' => Visit::STATUS_CHECKED_IN, 'check_out_time' => null])->count(),
            ];
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            $data = ['totalUsers' => 0, 'totalVisitors' => 0, 'totalVisits' => 0, 'activeVisits' => 0];
        }
        return $this->render('dashboard', $data);
    }

    public function actionStats(): Response
    {
        $this->requireRole(User::ROLE_ADMIN);

        Yii::$app->response->format = Response::FORMAT_JSON;
        $today = date('Y-m-d 00:00:00');

        try {
            return $this->asJson([
                'total' => (int) Visit::find()->count(),
                'inside' => (int) Visit::find()->where(['check_out_time' => null])->count(),
                'checked_out' => (int) Visit::find()->where(['not', ['check_out_time' => null]])->count(),
                'today' => (int) Visit::find()->where(['>=', 'check_in_time', $today])->count(),
            ]);
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);

            return $this->asJson([
                'total' => 0,
                'inside' => 0,
                'checked_out' => 0,
                'today' => 0,
            ]);
        }
    }

    public function actionActivity(): Response
    {
        $this->requireRole(User::ROLE_ADMIN);

        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $logs = AuditLog::find()
                ->orderBy(['created_at' => SORT_DESC])
                ->limit(10)
                ->all();

            return $this->asJson(array_map(static function (AuditLog $log): array {
                $timestamp = strtotime((string) $log->created_at);

                return [
                    'action' => (string) $log->action,
                    'description' => (string) $log->description,
                    'time' => $timestamp === false ? (string) $log->created_at : date('H:i:s', $timestamp),
                ];
            }, $logs));
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);

            return $this->asJson([]);
        }
    }

    public function actionReports(): string
    {
        $this->requireRole(User::ROLE_ADMIN);

        try {
            $data = [
                'totalVisits' => (int) Visit::find()->count(),
                'checkedIn' => (int) Visit::find()->where(['status' => Visit::STATUS_CHECKED_IN, 'check_out_time' => null])->count(),
                'checkedOut' => (int) Visit::find()->where(['status' => Visit::STATUS_CHECKED_OUT])->count(),
            ];
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            $data = ['totalVisits' => 0, 'checkedIn' => 0, 'checkedOut' => 0];
        }
        return $this->render('reports', $data);
    }
}

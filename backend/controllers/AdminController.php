<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\BaseController;
use common\models\AuditLog;
use common\models\Notification;
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

        $trendLabels = [];
        $trendValues = [];
        $hostLabels = [];
        $hostValues = [];

        try {
            $today = date('Y-m-d 00:00:00');
            $tomorrow = date('Y-m-d 00:00:00', strtotime('+1 day'));
            $trendStart = date('Y-m-d 00:00:00', strtotime('-6 days'));

            $dailyRows = Visit::find()
                ->select([
                    'day' => 'DATE(check_in_time)',
                    'count' => 'COUNT(*)',
                ])
                ->where(['>=', 'check_in_time', $trendStart])
                ->groupBy(['day'])
                ->asArray()
                ->all();
            $dailyCounts = [];
            foreach ($dailyRows as $row) {
                $dailyCounts[(string) $row['day']] = (int) $row['count'];
            }
            for ($offset = 6; $offset >= 0; $offset--) {
                $day = date('Y-m-d', strtotime('-' . $offset . ' days'));
                $trendLabels[] = date('D', strtotime($day));
                $trendValues[] = $dailyCounts[$day] ?? 0;
            }

            $hostRows = Visit::find()
                ->alias('v')
                ->select([
                    'host_name' => "COALESCE(u.username, 'Unassigned')",
                    'count' => 'COUNT(*)',
                ])
                ->leftJoin(['u' => User::tableName()], 'u.id = v.host_user_id')
                ->groupBy(['u.id', 'u.username'])
                ->orderBy(['count' => SORT_DESC])
                ->limit(8)
                ->asArray()
                ->all();
            foreach ($hostRows as $row) {
                $hostLabels[] = (string) $row['host_name'];
                $hostValues[] = (int) $row['count'];
            }

            $data = [
                'totalVisits' => (int) Visit::find()->count(),
                'checkedIn' => (int) Visit::find()->where(['status' => Visit::STATUS_CHECKED_IN, 'check_out_time' => null])->count(),
                'checkedOut' => (int) Visit::find()->where(['not', ['check_out_time' => null]])->count(),
                'totalVisitors' => (int) Visitor::find()->count(),
                'notificationCount' => (int) Notification::find()
                    ->where(['or', ['user_id' => (int) Yii::$app->user->id], ['user_id' => null]])
                    ->andWhere(['is_read' => 0])
                    ->count(),
                'recentVisits' => Visit::find()
                    ->with(['visitor', 'host'])
                    ->orderBy(['check_in_time' => SORT_DESC])
                    ->limit(30)
                    ->all(),
                'trendLabels' => $trendLabels,
                'trendValues' => $trendValues,
                'hostLabels' => $hostLabels,
                'hostValues' => $hostValues,
                'today' => (int) Visit::find()
                    ->where(['>=', 'check_in_time', $today])
                    ->andWhere(['<', 'check_in_time', $tomorrow])
                    ->count(),
            ];
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            $data = [
                'totalVisits' => 0,
                'checkedIn' => 0,
                'checkedOut' => 0,
                'totalVisitors' => 0,
                'notificationCount' => 0,
                'recentVisits' => [],
                'trendLabels' => $trendLabels,
                'trendValues' => $trendValues,
                'hostLabels' => $hostLabels,
                'hostValues' => $hostValues,
                'today' => 0,
            ];
        }
        return $this->render('reports', $data);
    }
}

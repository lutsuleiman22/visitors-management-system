<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\BaseController;
use common\models\User;
use common\models\Visit;
use Yii;

class AnalyticsController extends BaseController
{
    public function actionIndex(): string
    {
        $this->requireRole(User::ROLE_ADMIN);

        $trendLabels = [];
        $trendValues = [];
        $hostLabels = [];
        $hostValues = [];

        try {
            $today = new \DateTimeImmutable('today');
            $startDate = $today->modify('-29 days');
            $startTimestamp = $startDate->getTimestamp();

            $dailyRows = Visit::find()
                ->select([
                    'day' => 'DATE(FROM_UNIXTIME(created_at))',
                    'count' => 'COUNT(*)',
                ])
                ->where(['>=', 'created_at', $startTimestamp])
                ->groupBy(['day'])
                ->asArray()
                ->all();
            $dailyCounts = [];
            foreach ($dailyRows as $row) {
                $dailyCounts[(string) $row['day']] = (int) $row['count'];
            }
            for ($offset = 29; $offset >= 0; $offset--) {
                $day = $today->modify('-' . $offset . ' days');
                $trendLabels[] = $day->format('M j');
                $trendValues[] = $dailyCounts[$day->format('Y-m-d')] ?? 0;
            }

            $hostRows = Visit::find()
                ->alias('v')
                ->select([
                    'host_name' => "COALESCE(NULLIF(v.host_name, ''), u.username, 'Unassigned')",
                    'count' => 'COUNT(*)',
                ])
                ->leftJoin(['u' => User::tableName()], 'u.id = v.host_user_id')
                ->groupBy(['v.host_name', 'u.username'])
                ->orderBy(['count' => SORT_DESC])
                ->limit(10)
                ->asArray()
                ->all();
            foreach ($hostRows as $row) {
                $hostLabels[] = (string) $row['host_name'];
                $hostValues[] = (int) $row['count'];
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
        }

        return $this->render('index', [
            'trendLabels' => $trendLabels,
            'trendValues' => $trendValues,
            'hostLabels' => $hostLabels,
            'hostValues' => $hostValues,
        ]);
    }
}

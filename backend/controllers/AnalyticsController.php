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
        $range = (string) Yii::$app->request->get('range', 'weekly');
        if (!in_array($range, ['weekly', 'monthly'], true)) {
            $range = 'weekly';
        }

        try {
            $today = new \DateTimeImmutable('today');
            $startDate = $range === 'monthly' ? $today->modify('first day of this month') : $today->modify('-6 days');
            $startTimestamp = $startDate->getTimestamp();
            $endTimestamp = $today->setTime(23, 59, 59)->getTimestamp();

            $dailyRows = Visit::find()
                ->alias('v')
                ->select([
                    'day' => 'DATE(FROM_UNIXTIME(v.created_at))',
                    'count' => 'COUNT(*)',
                ])
                ->where(['between', 'v.created_at', $startTimestamp, $endTimestamp])
                ->groupBy(['day'])
                ->orderBy(['day' => SORT_ASC])
                ->asArray()
                ->all();
            $dailyCounts = [];
            foreach ($dailyRows as $row) {
                $dailyCounts[(string) $row['day']] = (int) $row['count'];
            }
            $dayCount = $startDate->diff($today)->days;
            for ($offset = $dayCount; $offset >= 0; $offset--) {
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
                ->where(['between', 'v.created_at', $startTimestamp, $endTimestamp])
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
            'range' => $range,
        ]);
    }
}

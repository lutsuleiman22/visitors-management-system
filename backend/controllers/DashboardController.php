<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\BaseController;
use common\models\User;
use common\models\Visit;
use common\services\BranchCatalog;
use Yii;
use yii\web\Response;

class DashboardController extends BaseController
{
    public function actionAnalytics(): string|Response
    {
        $this->requireRole(User::ROLE_ADMIN);
        $today = date('Y-m-d 00:00:00');
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
            $branchVisits = Visit::find()->select(['branch' => 'branch_code', 'count' => 'COUNT(*)'])->groupBy(['branch_code'])->orderBy(['count' => SORT_DESC])->asArray()->all();
            $departmentVisits = Visit::find()->select(['department' => "COALESCE(NULLIF(department_code, ''), 'Unassigned')", 'count' => 'COUNT(*)'])->groupBy(['department_code'])->orderBy(['count' => SORT_DESC])->asArray()->all();
            $genderVisits = Visit::find()->alias('v')->leftJoin('{{%visitors}} visitor', 'visitor.id = v.visitor_id')->select(['gender' => "COALESCE(NULLIF(visitor.gender, ''), 'Unspecified')", 'count' => 'COUNT(*)'])->groupBy(['visitor.gender'])->orderBy(['count' => SORT_DESC])->asArray()->all();
            $commonVisitors = Visit::find()->alias('v')->leftJoin('{{%visitors}} visitor', 'visitor.id = v.visitor_id')->select(['name' => 'visitor.full_name', 'phone' => 'visitor.phone_number', 'count' => 'COUNT(*)'])->groupBy(['v.visitor_id', 'visitor.full_name', 'visitor.phone_number'])->orderBy(['count' => SORT_DESC])->limit(10)->asArray()->all();
            $branchNames = BranchCatalog::all();
            foreach ($branchVisits as &$branchVisit) {
                $branchVisit['branch'] = $branchNames[$branchVisit['branch']] ?? $branchVisit['branch'];
            }
            unset($branchVisit);
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->asJson(['success' => false, 'data' => [], 'message' => 'Analytics data is temporarily unavailable.']);
        }
        return $this->asJson(['success' => true, 'data' => compact('daily', 'roles', 'hours', 'branchVisits', 'departmentVisits', 'genderVisits', 'commonVisitors'), 'message' => '']);
    }
}

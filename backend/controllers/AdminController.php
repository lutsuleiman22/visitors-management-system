<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\BaseController;
use common\models\User;
use common\models\Visit;
use common\models\Visitor;
use Yii;

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

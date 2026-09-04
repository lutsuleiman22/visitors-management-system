<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\BaseController;
use common\models\User;
use common\models\Visit;
use Yii;

class ReceptionController extends BaseController
{
    public function actionDashboard(): string
    {
        $this->requireRole(User::ROLE_ADMIN, User::ROLE_RECEPTION);
        $today = date('Y-m-d 00:00:00');

        try {
            $todayVisits = Visit::find()->with(['visitor', 'host'])->where(['>=', 'check_in_time', $today])->orderBy(['check_in_time' => SORT_DESC])->limit(100)->all();
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            $todayVisits = [];
        }
        return $this->render('dashboard', ['todayVisits' => $todayVisits]);
    }
}

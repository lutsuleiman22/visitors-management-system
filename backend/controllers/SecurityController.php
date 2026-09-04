<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\BaseController;
use common\models\User;
use common\models\Visit;
use Yii;

class SecurityController extends BaseController
{
    public function actionDashboard(): string
    {
        $this->requireRole(User::ROLE_ADMIN, User::ROLE_SECURITY);

        try {
            $activeVisits = Visit::find()->with(['visitor', 'host'])->where(['status' => Visit::STATUS_CHECKED_IN, 'check_out_time' => null])->orderBy(['check_in_time' => SORT_ASC])->limit(100)->all();
            $recentVisits = Visit::find()->with(['visitor', 'host'])->orderBy(['id' => SORT_DESC])->limit(20)->all();
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            $activeVisits = [];
            $recentVisits = [];
        }
        return $this->render('dashboard', ['activeVisits' => $activeVisits, 'recentVisits' => $recentVisits]);
    }
}

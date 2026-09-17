<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\BaseController;
use common\models\User;
use yii\web\Response;

class AnalyticsController extends BaseController
{
    public function actionIndex(): Response
    {
        $this->requireRole(User::ROLE_ADMIN);
        return $this->redirect(['/admin/dashboard']);
    }
}

<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\BaseController;
use common\models\User;
use common\models\Visit;
use Yii;

class SecurityController extends BaseController
{
    public function actionDashboard(): Response
    {
        Yii::$app->session->setFlash('warning', 'This section is not available because the system is configured for Admin and Reception only.');
        return $this->redirect(['/admin/dashboard']);
    }
}

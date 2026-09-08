<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\BaseController;
use common\models\AuditLog;
use common\models\User;
use Yii;
use yii\data\ActiveDataProvider;

class AuditLogController extends BaseController
{
    public function actionIndex(): string
    {
        $this->requireRole(User::ROLE_ADMIN);

        $query = AuditLog::find()->with('user')->orderBy(['created_at' => SORT_DESC]);

        $action = trim((string) Yii::$app->request->getQueryParam('action', ''));
        $userId = Yii::$app->request->getQueryParam('user_id');
        $modelName = trim((string) Yii::$app->request->getQueryParam('model', ''));
        $dateFrom = trim((string) Yii::$app->request->getQueryParam('date_from', ''));
        $dateTo = trim((string) Yii::$app->request->getQueryParam('date_to', ''));

        if ($action !== '') {
            $query->andWhere(['=', 'action', strtoupper($action)]);
        }

        if ($userId !== null && $userId !== '') {
            $query->andWhere(['user_id' => (int) $userId]);
        }

        if ($modelName !== '') {
            $query->andWhere(['like', 'model', $modelName]);
        }

        if ($dateFrom !== '') {
            $query->andWhere(['>=', 'created_at', $dateFrom . ' 00:00:00']);
        }

        if ($dateTo !== '') {
            $query->andWhere(['<=', 'created_at', $dateTo . ' 23:59:59']);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 25,
            ],
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'action' => $action,
            'userId' => $userId,
            'modelName' => $modelName,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }
}

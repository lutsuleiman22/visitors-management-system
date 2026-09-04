<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\BaseController;
use backend\models\VisitSearch;
use common\models\User;
use common\models\Visit;
use common\models\Visitor;
use common\services\AuditLogService;
use common\services\NotificationService;
use Yii;
use yii\data\ArrayDataProvider;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

/**
 * Visit management CRUD and evacuation roster.
 */
class VisitController extends BaseController
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'check-out' => ['POST'],
                ],
            ],
        ]);
    }

    public function actionIndex(): string|Response
    {
        $this->requireRole(User::ROLE_ADMIN, User::ROLE_RECEPTION);

        $searchModel = new VisitSearch();
        try {
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            $dataProvider = new ArrayDataProvider(['allModels' => []]);
            Yii::$app->session->setFlash('error', 'Visit data is temporarily unavailable.');
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Currently on-site visitors (check_out_time IS NULL).
     */
    public function actionEvacuation(): string|Response
    {
        $this->requireRole(User::ROLE_ADMIN, User::ROLE_SECURITY);

        try {
            $dataProvider = new ActiveDataProvider([
                'query' => Visit::find()
                    ->alias('v')
                    ->with(['visitor', 'host'])
                    ->where([
                        'and',
                        ['v.status' => Visit::STATUS_CHECKED_IN],
                        ['v.check_out_time' => null],
                    ])
                    ->orderBy(['v.check_in_time' => SORT_ASC]),
                'pagination' => false,
                'sort' => false,
            ]);
            $count = (int) $dataProvider->getTotalCount();
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            $dataProvider = new ArrayDataProvider(['allModels' => []]);
            $count = 0;
            Yii::$app->session->setFlash('error', 'Active visitor data is temporarily unavailable.');
        }

        return $this->render('evacuation', [
            'dataProvider' => $dataProvider,
            'count' => $count,
            'generatedAt' => time(),
        ]);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionView(int $id): string|Response
    {
        $this->requireRole(User::ROLE_ADMIN, User::ROLE_RECEPTION, User::ROLE_SECURITY);

        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * @return string|Response
     */
    public function actionCreate(): string|Response
    {
        $this->requireRole(User::ROLE_ADMIN, User::ROLE_RECEPTION);

        $model = new Visit();
        $model->status = Visit::STATUS_CHECKED_IN;
        $model->qr_code_hash = Visit::generateQrCodeHash();
        $model->check_in_time = date('Y-m-d H:i:s');

        try {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                AuditLogService::logAction('create-visit', 'Visit #' . $model->id . ' created.');
                NotificationService::createNotification('New visit recorded.', 'info');
                Yii::$app->session->setFlash('success', 'Visit created successfully.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'Unable to create visit at this time.');
        }

        return $this->render('create', [
            'model' => $model,
            'visitors' => $this->visitorList(),
            'hosts' => $this->hostList(),
        ]);
    }

    /**
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate(int $id): string|Response
    {
        $this->requireRole(User::ROLE_ADMIN);

        $model = $this->findModel($id);

        try {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                AuditLogService::logAction('update-visit', 'Visit #' . $model->id . ' updated.');
                Yii::$app->session->setFlash('success', 'Visit updated successfully.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'Unable to update visit at this time.');
        }

        return $this->render('update', [
            'model' => $model,
            'visitors' => $this->visitorList(),
            'hosts' => $this->hostList(),
        ]);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionDelete(int $id): Response
    {
        $this->requireRole(User::ROLE_ADMIN);

        try {
            $this->findModel($id)->delete();
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'Unable to delete visit at this time.');
            return $this->redirect(['/site/index']);
        }
        AuditLogService::logAction('delete-visit', 'Visit #' . $id . ' deleted.');
        Yii::$app->session->setFlash('success', 'Visit deleted.');

        return $this->redirect(['/site/index']);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionCheckOut(int $id): Response
    {
        $this->requireRole(User::ROLE_ADMIN, User::ROLE_RECEPTION);

        $model = $this->findModel($id);

        try {
            if (!$model->isCheckedIn()) {
                Yii::$app->session->setFlash('warning', 'This visit has already been checked out.');
            } elseif ($model->checkOut()) {
                AuditLogService::logAction('check-out', 'Visitor checked out from visit #' . $model->id);
                NotificationService::createNotification('Visitor checked out: ' . ($model->visitor->full_name ?? 'Unknown'), 'success');
                Yii::$app->session->setFlash('success', 'Visitor checked out successfully.');
            } else {
                Yii::$app->session->setFlash('error', 'Unable to check out this visitor.');
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'Unable to check out this visitor at this time.');
        }

        return $this->redirect(Yii::$app->request->referrer ?: ['view', 'id' => $id]);
    }

    /**
     * @throws NotFoundHttpException
     */
    protected function findModel(int $id): Visit
    {
        try {
            $model = Visit::find()->with(['visitor', 'host'])->where(['id' => $id])->one();
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            throw new ServerErrorHttpException('Visit data is temporarily unavailable.');
        }
        if ($model === null) {
            throw new NotFoundHttpException('The requested visit does not exist.');
        }

        return $model;
    }

    /**
     * @return array<int, string>
     */
    protected function visitorList(): array
    {
        $list = [];
        foreach (Visitor::find()->orderBy(['full_name' => SORT_ASC])->all() as $visitor) {
            $label = $visitor->full_name;
            if ($visitor->national_id) {
                $label .= ' (' . $visitor->national_id . ')';
            }
            $list[$visitor->id] = $label;
        }

        return $list;
    }

    /**
     * @return array<int, string>
     */
    protected function hostList(): array
    {
        return User::find()
            ->select(['username', 'id'])
            ->where(['status' => User::STATUS_ACTIVE])
            ->indexBy('id')
            ->orderBy(['username' => SORT_ASC])
            ->column();
    }
}

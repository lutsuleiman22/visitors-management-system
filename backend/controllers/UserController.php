<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\BaseController;
use common\models\User;
use common\services\AuditLogService;
use common\services\BranchCatalog;
use Yii;
use yii\data\ArrayDataProvider;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class UserController extends BaseController
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
                'verbs' => ['class' => VerbFilter::class, 'actions' => ['delete' => ['POST'], 'toggle-status' => ['POST'], 'approve' => ['POST']]],
        ]);
    }

    public function actionIndex(): string
    {
        $this->requireRole(User::ROLE_ADMIN);
        $branches = BranchCatalog::all();
        $selectedBranch = trim((string) Yii::$app->request->get('branch', ''));
        try {
            $query = User::find()->orderBy(['username' => SORT_ASC]);
            if ($selectedBranch !== '' && array_key_exists($selectedBranch, $branches)) {
                $query->andWhere(['branch_code' => $selectedBranch]);
            } else {
                $selectedBranch = '';
            }
            $dataProvider = new ActiveDataProvider(['query' => $query]);
            $dataProvider->getTotalCount();
            $pendingUsers = User::find()
                ->where(['role' => User::ROLE_RECEPTION, 'status' => User::STATUS_INACTIVE])
                ->andFilterWhere($selectedBranch === '' ? [] : ['branch_code' => $selectedBranch])
                ->orderBy(['created_at' => SORT_ASC])
                ->all();
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            $dataProvider = new ArrayDataProvider(['allModels' => []]);
            $pendingUsers = [];
            Yii::$app->session->setFlash('error', 'User data is temporarily unavailable.');
        }
        return $this->render('index', ['dataProvider' => $dataProvider, 'pendingUsers' => $pendingUsers, 'branches' => $branches, 'selectedBranch' => $selectedBranch]);
    }

    public function actionApprove(int $id): Response
    {
        $this->requireRole(User::ROLE_ADMIN);
        $user = $this->findModel($id);

        if ($user->role !== User::ROLE_RECEPTION || $user->status !== User::STATUS_INACTIVE) {
            Yii::$app->session->setFlash('warning', 'Only pending reception accounts can be approved.');
            return $this->redirect(['index']);
        }

        if ($user->updateAttributes(['status' => User::STATUS_ACTIVE])) {
            AuditLogService::logAction('approve-user', 'Reception account #' . $user->id . ' approved.');
            Yii::$app->session->setFlash('success', 'Reception account approved successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Unable to approve this reception account.');
        }

        return $this->redirect(['index', 'branch' => $user->branch_code]);
    }

    public function actionCreate(): string|Response
    {
        $this->requireRole(User::ROLE_ADMIN);
        $model = new User();
        try {
            if ($model->load(Yii::$app->request->post())) {
                $model->setPassword((string) Yii::$app->request->post('password'));
                $model->generateAuthKey();
                if ($model->save()) {
                    AuditLogService::logAction('create-user', 'User #' . $model->id . ' created.');
                    Yii::$app->session->setFlash('success', 'User created successfully.');
                    return $this->redirect(['index']);
                }
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'Unable to create user at this time.');
        }
        return $this->render('create', ['model' => $model, 'branches' => BranchCatalog::all()]);
    }

    public function actionUpdate(int $id): string|Response
    {
        $this->requireRole(User::ROLE_ADMIN);
        $model = $this->findModel($id);
        try {
            if ($model->load(Yii::$app->request->post())) {
                $password = (string) Yii::$app->request->post('password');
                if ($password !== '') {
                    $model->setPassword($password);
                }
                if ($model->save()) {
                    AuditLogService::logAction('update-user', 'User #' . $model->id . ' updated.');
                    Yii::$app->session->setFlash('success', 'User updated successfully.');
                    return $this->redirect(['index']);
                }
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'Unable to update user at this time.');
        }
        return $this->render('update', ['model' => $model, 'branches' => BranchCatalog::all()]);
    }

    public function actionDelete(int $id): Response
    {
        return $this->actionToggleStatus($id);
    }

    public function actionToggleStatus(int $id): Response
    {
        $this->requireRole(User::ROLE_ADMIN);
        if ((int) Yii::$app->user->id === $id) {
            Yii::$app->session->setFlash('error', 'You cannot change your own account status.');
            return $this->redirect(['index']);
        }
        try {
            $user = $this->findModel($id);
            $newStatus = $user->status === User::STATUS_ACTIVE ? User::STATUS_DELETED : User::STATUS_ACTIVE;
            $user->updateAttributes(['status' => $newStatus]);
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'Unable to change user status at this time.');
            return $this->redirect(['index']);
        }
        $action = $newStatus === User::STATUS_ACTIVE ? 'activate-user' : 'deactivate-user';
        $label = $newStatus === User::STATUS_ACTIVE ? 'activated' : 'deactivated';
        AuditLogService::logAction($action, 'User #' . $id . ' ' . $label . '.');
        Yii::$app->session->setFlash('success', 'User ' . $label . '.');
        return $this->redirect(['index', 'branch' => $user->branch_code]);
    }

    /** @throws NotFoundHttpException */
    protected function findModel(int $id): User
    {
        try {
            $model = User::findOne($id);
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            throw new \yii\web\ServerErrorHttpException('User data is temporarily unavailable.');
        }
        if ($model === null) {
            throw new NotFoundHttpException('The requested user does not exist.');
        }
        return $model;
    }
}

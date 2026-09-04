<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\BaseController;
use common\models\User;
use common\services\AuditLogService;
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
            'verbs' => ['class' => VerbFilter::class, 'actions' => ['delete' => ['POST']]],
        ]);
    }

    public function actionIndex(): string
    {
        $this->requireRole(User::ROLE_ADMIN);
        try {
            $dataProvider = new ActiveDataProvider(['query' => User::find()->orderBy(['username' => SORT_ASC])]);
            $dataProvider->getTotalCount();
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            $dataProvider = new ArrayDataProvider(['allModels' => []]);
            Yii::$app->session->setFlash('error', 'User data is temporarily unavailable.');
        }
        return $this->render('index', ['dataProvider' => $dataProvider]);
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
        return $this->render('create', ['model' => $model]);
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
        return $this->render('update', ['model' => $model]);
    }

    public function actionDelete(int $id): Response
    {
        $this->requireRole(User::ROLE_ADMIN);
        if ((int) Yii::$app->user->id === $id) {
            Yii::$app->session->setFlash('error', 'You cannot delete your own account.');
            return $this->redirect(['index']);
        }
        try {
            $this->findModel($id)->updateAttributes(['status' => User::STATUS_DELETED]);
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'Unable to deactivate user at this time.');
            return $this->redirect(['index']);
        }
        AuditLogService::logAction('delete-user', 'User #' . $id . ' deactivated.');
        Yii::$app->session->setFlash('success', 'User deactivated.');
        return $this->redirect(['index']);
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

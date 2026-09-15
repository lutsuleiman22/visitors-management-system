<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\BaseController;
use common\components\AuditLogger;
use common\models\LoginForm;
use common\models\User;
use common\services\AuditLogService;
use common\services\NotificationService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\ErrorAction;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

/**
 * Site controller
 */
class SiteController extends BaseController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return [
            'error' => [
                'class' => ErrorAction::class,
            ],
        ];
    }

    /**
    * Admin dashboard entry point.
     *
     * @return string
     */
    public function actionIndex(): string|Response
    {
        $role = (string) Yii::$app->user->identity->role;
        if ($role === User::ROLE_ADMIN) {
            return $this->redirect(['/admin/dashboard']);
        }

        throw new ForbiddenHttpException('You are not authorized to access the backend dashboard.');
    }

    /**
     * Login action.
     *
     * @return string|Response
     */
    public function actionLogin(): string|Response
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $this->layout = 'blank';

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            if (!Yii::$app->user->identity instanceof User || !Yii::$app->user->identity->isAdmin()) {
                Yii::$app->user->logout();
                $model->addError('username', 'Only administrator accounts can access the backend.');
                $model->password = '';
                return $this->render('login', ['model' => $model]);
            }

            AuditLogService::logAction('login', 'User logged in successfully.');
            AuditLogger::log('LOGIN', 'User', Yii::$app->user->id, 'User logged in');
            NotificationService::createNotification('New login: ' . Yii::$app->user->identity->username, 'info', (int) Yii::$app->user->id);
            return $this->redirect(['/admin/dashboard']);
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout(): Response
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
}

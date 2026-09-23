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
            $query = User::find()->andWhere(['!=', 'status', User::STATUS_DELETED])->orderBy(['username' => SORT_ASC]);
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

            if ($this->sendAccountApprovedEmail($user)) {
                Yii::$app->session->setFlash('success', 'Reception account approved. A notification email was sent to ' . $user->email . '.');
            } else {
                Yii::$app->session->setFlash('warning', 'Reception account approved, but the notification email could not be sent.');
            }
        } else {
            Yii::$app->session->setFlash('error', 'Unable to approve this reception account.');
        }

        return $this->redirect(['index', 'branch' => $user->branch_code]);
    }

    /**
     * Notifies a reception self-signup user by email once their account is approved.
     * They already chose their own password at signup, so this just tells them to log in.
     */
    protected function sendAccountApprovedEmail(User $model): bool
    {
        try {
            $frontendHostInfo = rtrim((string) Yii::$app->params['frontendHostInfo'], '/');
            $loginLink = $frontendHostInfo . '/index.php?' . http_build_query(['r' => 'site/login']);

            return Yii::$app->mailer
                ->compose(
                    ['html' => 'accountApproved-html', 'text' => 'accountApproved-text'],
                    ['user' => $model, 'loginLink' => $loginLink, 'appName' => Yii::$app->name],
                )
                ->setFrom([(string) Yii::$app->params['senderEmail'] => (string) Yii::$app->params['senderName']])
                ->setTo($model->email)
                ->setSubject('Your account on ' . Yii::$app->name . ' has been approved')
                ->send();
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return false;
        }
    }

    public function actionCreate(): string|Response
    {
        $this->requireRole(User::ROLE_ADMIN);
        $model = new User();
        try {
            if ($model->load(Yii::$app->request->post())) {
                if (!array_key_exists($model->role, User::creatableRoleList())) {
                    Yii::$app->session->setFlash('error', 'You can only create Reception or Security accounts from this form.');
                    return $this->render('create', ['model' => $model, 'branches' => BranchCatalog::all()]);
                }

                // Admin does not set the password directly. Generate a random,
                // unguessable password to satisfy the NOT NULL column — nobody
                // is told this value. The user sets their own password via the
                // emailed invite link instead.
                $model->setPassword(Yii::$app->security->generateRandomString(32));
                $model->generateAuthKey();

                if ($model->save()) {
                    AuditLogService::logAction('create-user', 'User #' . $model->id . ' created.');

                    if ((int) $model->status === User::STATUS_ACTIVE) {
                        if ($this->sendAccountInviteEmail($model)) {
                            Yii::$app->session->setFlash('success', 'User created. An invite email was sent to ' . $model->email . '.');
                        } else {
                            Yii::$app->session->setFlash('warning', 'User created, but the invite email could not be sent. Please check the mailer configuration.');
                        }
                    } else {
                        Yii::$app->session->setFlash('success', 'User created successfully. Activate the account to send the invite email.');
                    }

                    return $this->redirect(['index']);
                }

                if ($model->hasErrors()) {
                    Yii::$app->session->setFlash('error', 'Unable to create user: ' . implode(' ', $model->getFirstErrors()));
                }
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'Unable to create user at this time.');
        }
        return $this->render('create', ['model' => $model, 'branches' => BranchCatalog::all()]);
    }

    /**
     * Sends the "set your password" invite email to a newly created, active user.
     */
    protected function sendAccountInviteEmail(User $model): bool
    {
        try {
            $model->generatePasswordResetToken();
            if (!$model->save(false, ['password_reset_token'])) {
                return false;
            }

            $frontendHostInfo = rtrim((string) Yii::$app->params['frontendHostInfo'], '/');
            $setPasswordLink = $frontendHostInfo . '/index.php?' . http_build_query([
                'r' => 'site/reset-password',
                'token' => $model->password_reset_token,
            ]);

            return Yii::$app->mailer
                ->compose(
                    ['html' => 'accountInvite-html', 'text' => 'accountInvite-text'],
                    ['user' => $model, 'setPasswordLink' => $setPasswordLink, 'appName' => Yii::$app->name],
                )
                ->setFrom([(string) Yii::$app->params['senderEmail'] => (string) Yii::$app->params['senderName']])
                ->setTo($model->email)
                ->setSubject('Your account on ' . Yii::$app->name)
                ->send();
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return false;
        }
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

                if ($model->hasErrors()) {
                    Yii::$app->session->setFlash('error', 'Unable to update user: ' . implode(' ', $model->getFirstErrors()));
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
        $this->requireRole(User::ROLE_ADMIN);
        if ((int) Yii::$app->user->id === $id) {
            Yii::$app->session->setFlash('error', 'You cannot delete your own account.');
            return $this->redirect(['index']);
        }
        try {
            $user = $this->findModel($id);
            if (!in_array($user->role, [User::ROLE_RECEPTION, User::ROLE_SECURITY], true)) {
                Yii::$app->session->setFlash('error', 'Only Reception or Security accounts can be deleted.');
                return $this->redirect(['index', 'branch' => $user->branch_code]);
            }
            if ($user->status === User::STATUS_DELETED) {
                Yii::$app->session->setFlash('warning', 'This user is already deleted.');
                return $this->redirect(['index', 'branch' => $user->branch_code]);
            }
            $branch = $user->branch_code;
            $user->updateAttributes(['status' => User::STATUS_DELETED]);
            AuditLogService::logAction('delete-user', 'User #' . $id . ' deleted.');
            Yii::$app->session->setFlash('success', 'User deleted.');
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'Unable to delete this user at this time.');
            return $this->redirect(['index']);
        }
        return $this->redirect(['index', 'branch' => $branch]);
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

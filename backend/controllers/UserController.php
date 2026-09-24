<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\BaseController;
use common\models\User;
use common\services\AuditLogService;
use common\services\BranchCatalog;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
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

        $user->generatePasswordResetToken();

        if ($user->save(false, ['password_reset_token', 'updated_at']) && $user->updateAttributes(['status' => User::STATUS_ACTIVE])) {
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
            $resetLink = $frontendHostInfo . '/index.php?' . http_build_query([
                'r' => 'site/reset-password',
                'token' => $model->password_reset_token,
            ]);

            $transport = Transport::fromDsn('smtp://faridasleyman@gmail.com:vpyeoroajaxjepqz@smtp.gmail.com:587?encryption=tls&auth_mode=login');
            $mailer = new Mailer($transport);

            $email = (new Email())
                ->from('faridasleyman@gmail.com')
                ->to($model->email)
                ->subject('Your account on ' . Yii::$app->name . ' has been approved')
                ->html(
                    '<p>Hello ' . htmlspecialchars((string) $model->username, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ',</p>' .
                    '<p>Your reception account has been approved.</p>' .
                    '<p>Please set your new password using the link below before you begin your shift.</p>' .
                    '<p><a href="' . htmlspecialchars($resetLink, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">Set your new password</a></p>' .
                    '<p>Or log in here: <a href="' . htmlspecialchars($loginLink, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">Login</a></p>'
                );

            $mailer->send($email);
            return true;
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
                    Yii::$app->session->setFlash('error', 'You can only create Admin or Reception accounts from this form.');
                    return $this->render('create', ['model' => $model, 'branches' => BranchCatalog::all()]);
                }

                // Generate a temporary password and include it in the invite email.
                // The user logs in with it, waits for admin approval, and then sets a new password.
                $generatedPassword = Yii::$app->security->generateRandomString(8);
                $model->setPassword($generatedPassword);
                $model->generateAuthKey();

                if ($model->save()) {
                    AuditLogService::logAction('create-user', 'User #' . $model->id . ' created.');

                    if (in_array((int) $model->status, [User::STATUS_ACTIVE, User::STATUS_INACTIVE], true)) {
                        if ($this->sendAccountInviteEmail($model, $generatedPassword)) {
                            $statusText = (int) $model->status === User::STATUS_INACTIVE ? 'pending approval' : 'active account';
                            Yii::$app->session->setFlash('success', 'User created. Login details were sent to ' . $model->email . ' for the ' . $statusText . ' flow.');
                        } else {
                            Yii::$app->session->setFlash('warning', 'User created, but the invite email could not be sent. Please check the mailer configuration.');
                        }
                    } else {
                        Yii::$app->session->setFlash('success', 'User created successfully.');
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
     * Sends the login instructions to a newly created user.
     * The message includes the username and temporary password so the user can sign in,
     * wait for admin approval, and then set a new password once approved.
     */
    protected function sendAccountInviteEmail(User $model, ?string $generatedPassword = null): bool
    {
        try {
            $frontendHostInfo = rtrim((string) Yii::$app->params['frontendHostInfo'], '/');
            $loginLink = $frontendHostInfo . '/index.php?' . http_build_query([
                'r' => 'site/login',
            ]);

            $transport = Transport::fromDsn('smtp://faridasleyman@gmail.com:vpyeoroajaxjepqz@smtp.gmail.com:587?encryption=tls&auth_mode=login');
            $mailer = new Mailer($transport);

            $approvalNote = (int) $model->status === User::STATUS_INACTIVE
                ? '<p>Your account is pending admin approval. Log in with the credentials below, wait for approval, then set a new password before starting your shift.</p>'
                : '<p>Log in with the credentials below and continue to your desk.</p>';

            $emailBody =
                '<p>Hello ' . htmlspecialchars((string) $model->username, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ',</p>' .
                '<p>Your account has been created in the system.</p>' .
                '<p><strong>Username:</strong> ' . htmlspecialchars((string) $model->username, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>' .
                '<p><strong>Temporary password:</strong> ' . htmlspecialchars((string) $generatedPassword, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>' .
                $approvalNote .
                '<p><a href="' . htmlspecialchars($loginLink, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">Login here</a></p>';

            $email = (new Email())
                ->from('faridasleyman@gmail.com')
                ->to($model->email)
                ->subject('Your account on ' . Yii::$app->name)
                ->html($emailBody);

            $mailer->send($email);
            return true;
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
            if (!in_array($user->role, [User::ROLE_ADMIN, User::ROLE_RECEPTION, 'security'], true)) {
                Yii::$app->session->setFlash('error', 'Only Admin, Reception, or legacy Security accounts can be deleted.');
                return $this->redirect(['index', 'branch' => $user->branch_code]);
            }
            $branch = $user->branch_code;
            $user->delete();
            AuditLogService::logAction('delete-user', 'User #' . $id . ' permanently deleted.');
            Yii::$app->session->setFlash('success', 'User deleted permanently.');
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

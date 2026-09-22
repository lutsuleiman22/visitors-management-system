<?php

declare(strict_types=1);

namespace frontend\controllers;

use common\models\LoginForm;
use common\models\ReceptionShift;
use common\models\User;
use common\services\AuditLogService;
use frontend\models\ContactForm;
use frontend\models\ReceptionSignupForm;
use common\services\BranchCatalog;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResendVerificationEmailForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\VerifyEmailForm;
use Yii;
use yii\base\InvalidArgumentException;
use yii\captcha\CaptchaAction;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\mail\MailerInterface;
use yii\web\BadRequestHttpException;
use yii\web\Cookie;
use yii\web\Controller;
use yii\web\ErrorAction;
use yii\web\Response;

/**
 * Site controller
 */
class SiteController extends Controller
{
    public function __construct(
        $id,
        $module,
        private readonly MailerInterface $mailer,
        $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'signup', 'start-shift', 'close-shift'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['start-shift'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => static fn (): bool => Yii::$app->user->identity?->isReception() === true,
                    ],
                    [
                        'actions' => ['close-shift'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => static fn (): bool => Yii::$app->user->identity?->isReception() === true,
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['GET', 'POST'],
                    'change-branch' => ['post'],
                    'start-shift' => ['post'],
                    'close-shift' => ['post'],
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
            'captcha' => [
                'class' => CaptchaAction::class,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex(): string|Response
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['login']);
        }

        if (Yii::$app->user->identity?->isReception() === true) {
            return $this->redirect(['reception-dashboard']);
        }

        $branches = BranchCatalog::all();
        $selectedBranch = (string) Yii::$app->session->get('pbz_branch', '');

        if (Yii::$app->request->isPost) {
            $selectedBranch = trim((string) Yii::$app->request->post('branch', ''));
            if (!array_key_exists($selectedBranch, $branches)) {
                Yii::$app->session->setFlash('error', 'Please select a valid PBZ branch.');
                $selectedBranch = '';
            } else {
                Yii::$app->session->set('pbz_branch', $selectedBranch);
                Yii::$app->session->setFlash('success', 'Branch selected: ' . $branches[$selectedBranch]);
            }
        }

        return $this->render('index', ['branches' => $branches, 'selectedBranch' => $selectedBranch]);
    }

    /**
     * Logs in a user.
     *
     * @return string|Response
     */
    public function actionLogin(): string|Response
    {
        if (!Yii::$app->user->isGuest) {
            $identity = Yii::$app->user->identity;
            if ($identity instanceof User && $identity->isReception()) {
                $activeShift = ReceptionShift::findOne([
                    'user_id' => (int) $identity->id,
                    'branch_code' => (string) $identity->branch_code,
                    'status' => ReceptionShift::STATUS_OPEN,
                ]);
                if ($activeShift !== null) {
                    Yii::$app->session->set('pbz_branch', (string) $identity->branch_code);
                    Yii::$app->session->set('reception_shift', $this->shiftSessionData($activeShift));
                    return $this->redirect(['/site/reception-dashboard']);
                }
            }
            return $this->goHome();
        }

        $model = new LoginForm();

        if ($model->load(Yii::$app->request->post())) {
            $pendingUser = User::find()->where(['username' => $model->username])->one();
            if ($pendingUser !== null && $pendingUser->role === User::ROLE_RECEPTION && $pendingUser->status === User::STATUS_INACTIVE) {
                $model->addError('username', 'Your reception account is waiting for Admin approval.');
            } elseif ($model->login()) {
                if (!Yii::$app->user->identity instanceof User || !Yii::$app->user->identity->isReception()) {
                    Yii::$app->user->logout();
                    $model->addError('username', 'Only reception accounts can access the frontend desk.');
                } else {
                    $selectedBranch = (string) Yii::$app->user->identity->branch_code;
                    Yii::$app->session->set('pbz_branch', $selectedBranch);
                    $activeShift = ReceptionShift::findOne([
                        'branch_code' => $selectedBranch,
                        'status' => ReceptionShift::STATUS_OPEN,
                    ]);
                    if ($activeShift !== null && (int) $activeShift->user_id !== (int) Yii::$app->user->id) {
                        Yii::$app->user->logout(true);
                        $model->addError('username', 'Another reception user has an active shift at this branch. That shift must be closed first.');
                    } else {
                        if ($activeShift !== null) {
                            Yii::$app->session->set('reception_shift', $this->shiftSessionData($activeShift));
                        }
                        return $this->redirect(['/site/reception-dashboard']);
                    }
                }
            }
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionReceptionSignup(): string|Response
    {
        $model = new ReceptionSignupForm();

        if ($model->load(Yii::$app->request->post())) {
            $user = $model->createReception();
            if ($user !== null) {
                Yii::$app->session->setFlash('success', 'Account created. Please wait for Admin approval before signing in.');
                return $this->redirect(['/site/login']);
            }
        }

        return $this->render('reception-signup', ['model' => $model]);
    }

    public function actionReceptionDashboard(): string|Response
    {
        $identity = Yii::$app->user->identity;
        $branch = (string) Yii::$app->session->get('pbz_branch', '');
        if (!$identity instanceof User || !$identity->isReception() || $branch === '') {
            Yii::$app->session->setFlash('error', 'Please sign in with an approved reception account first.');
            return $this->redirect(['/site/index']);
        }

        $activeShift = ReceptionShift::findOne([
            'branch_code' => $branch,
            'status' => ReceptionShift::STATUS_OPEN,
        ]);
        $shiftStarted = $activeShift !== null && (int) $activeShift->user_id === (int) $identity->id;
        $shift = $shiftStarted ? $this->shiftSessionData($activeShift) : [];
        if ($shiftStarted) {
            Yii::$app->session->set('reception_shift', $shift);
        }

        if (!$shiftStarted) {
            return $this->render('reception-dashboard', [
                'branchName' => BranchCatalog::all()[$branch] ?? $branch,
                'visits' => [],
                'shiftStarted' => false,
                'shift' => Yii::$app->session->get('reception_last_shift', []),
            ]);
        }

        $visits = \common\models\Visit::find()
            ->with(['visitor', 'host'])
            ->where(['branch_code' => $branch])
            ->andWhere(['>=', 'check_in_time', date('Y-m-d 00:00:00')])
            ->andWhere(['<', 'check_in_time', date('Y-m-d 00:00:00', strtotime('+1 day'))])
            ->orderBy(['check_in_time' => SORT_DESC])
            ->all();

        return $this->render('reception-dashboard', [
            'branchName' => BranchCatalog::all()[$branch] ?? $branch,
            'visits' => $visits,
            'shiftStarted' => true,
            'shift' => Yii::$app->session->get('reception_shift', []),
        ]);
    }

    public function actionStartShift(): Response
    {
        $identity = Yii::$app->user->identity;
        $branch = (string) Yii::$app->session->get('pbz_branch', '');
        if (!$identity instanceof User || !$identity->isReception() || $branch === '') {
            Yii::$app->session->setFlash('error', 'Please select your branch and sign in first.');
            return $this->redirect(['/site/index']);
        }

        $activeShift = ReceptionShift::findOne([
            'branch_code' => $branch,
            'status' => ReceptionShift::STATUS_OPEN,
        ]);
        if ($activeShift !== null && (int) $activeShift->user_id !== (int) $identity->id) {
            Yii::$app->session->setFlash('error', 'Another reception user has an active shift at this branch. Close it before starting a new shift.');
            return $this->redirect(['reception-dashboard']);
        }

        if ($activeShift === null) {
            $activeShift = new ReceptionShift();
            $activeShift->user_id = (int) $identity->id;
            $activeShift->branch_code = $branch;
            $activeShift->started_at = date('Y-m-d H:i:s');
            $activeShift->status = ReceptionShift::STATUS_OPEN;
            if (!$activeShift->save()) {
                Yii::$app->session->setFlash('error', 'Unable to start the shift. Please try again.');
                return $this->redirect(['reception-dashboard']);
            }
        }
        Yii::$app->session->set('reception_shift', $this->shiftSessionData($activeShift));
        AuditLogService::logAction('start-shift', 'Reception shift started for branch ' . $branch . '.');
        Yii::$app->session->setFlash('success', 'Shift started for ' . (BranchCatalog::all()[$branch] ?? $branch) . '.');

        return $this->redirect(['reception-dashboard']);
    }

    public function actionCloseShift(): Response
    {
        $identity = Yii::$app->user->identity;
        $branch = (string) Yii::$app->session->get('pbz_branch', '');
        if (!$identity instanceof User || !$identity->isReception() || $branch === '') {
            Yii::$app->session->setFlash('error', 'Please select your branch and sign in first.');
            return $this->redirect(['/site/index']);
        }

        $shift = ReceptionShift::findOne([
            'branch_code' => $branch,
            'status' => ReceptionShift::STATUS_OPEN,
        ]);
        if ($shift !== null && (int) $shift->user_id === (int) $identity->id) {
            $shift->status = ReceptionShift::STATUS_CLOSED;
            $shift->stopped_at = date('Y-m-d H:i:s');
            $shift->save(false);
            AuditLogService::logAction('close-shift', 'Reception shift closed for branch ' . $branch . '.');
            Yii::$app->session->set('reception_last_shift', [
                'branch' => $branch,
                'started_at' => (string) $shift->started_at,
                'stopped_at' => (string) $shift->stopped_at,
            ]);
        }
        Yii::$app->session->remove('reception_shift');
        Yii::$app->session->remove('visitor_checkin_data');
        Yii::$app->session->remove('visitor_checkout_data');
        Yii::$app->session->setFlash('success', 'Shift closed successfully.');

        return $this->redirect(['reception-dashboard']);
    }

    /**
     * Logs out the current user.
     *
     * @return Response
     */
    public function actionLogout(): Response
    {
        Yii::$app->user->logout(true);
        Yii::$app->response->cookies->remove(new Cookie([
            'name' => '_identity-frontend',
            'path' => '/',
        ]));
        Yii::$app->session->destroy();

        return $this->redirect(['/site/login']);
    }

    public function actionChangeBranch(): Response
    {
        $activeShift = Yii::$app->session->get('reception_shift', []);
        if (is_array($activeShift) && (string) ($activeShift['started_at'] ?? '') !== '') {
            Yii::$app->session->setFlash('error', 'Close the active shift before changing branch.');
            return $this->redirect(['reception-dashboard']);
        }

        Yii::$app->user->logout(true);
        Yii::$app->session->remove('pbz_branch');
        Yii::$app->session->remove('visitor_checkin_data');
        Yii::$app->session->remove('visitor_checkout_data');
        Yii::$app->session->setFlash('success', 'Choose the new PBZ branch to continue.');

        return $this->redirect(['/site/index']);
    }

    /** @return array{user_id: int, branch: string, started_at: string} */
    private function shiftSessionData(ReceptionShift $shift): array
    {
        return [
            'user_id' => (int) $shift->user_id,
            'branch' => (string) $shift->branch_code,
            'started_at' => (string) $shift->started_at,
        ];
    }

    /**
     * Displays contact page.
     *
     * @return string|Response
     */
    public function actionContact(): string|Response
    {
        return $this->redirect(['/site/index']);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout(): string
    {
        return $this->redirect(['/site/index']);
    }

    /**
     * Signs user up.
     *
     * @return string|Response
     */
    public function actionSignup(): string|Response
    {
        $model = new SignupForm();

        $signed = $model->load(Yii::$app->request->post()) && $model->signup(
            $this->mailer,
            Yii::$app->params['supportEmail'],
            Yii::$app->name,
        );

        if ($signed) {
            Yii::$app->session->setFlash('success', 'Thank you for registration. Please check your inbox for verification email.');
            return $this->goHome();
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return string|Response
     */
    public function actionRequestPasswordReset(): string|Response
    {
        $model = new PasswordResetRequestForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $sent = $model->sendEmail(
                $this->mailer,
                Yii::$app->params['supportEmail'],
                Yii::$app->name,
            );

            if ($sent) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            }

            Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return string|Response
     * @throws BadRequestHttpException
     */
    public function actionResetPassword(string $token): string|Response
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    /**
     * Verify email address
     *
     * @param string $token
     * @return Response
     * @throws BadRequestHttpException
     */
    public function actionVerifyEmail(string $token): Response
    {
        try {
            $model = new VerifyEmailForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->verifyEmail()) {
            Yii::$app->session->setFlash('success', 'Your email has been confirmed!');
            return $this->goHome();
        }

        Yii::$app->session->setFlash('error', 'Sorry, we are unable to verify your account with provided token.');
        return $this->goHome();
    }

    /**
     * Resend verification email
     *
     * @return string|Response
     */
    public function actionResendVerificationEmail(): string|Response
    {
        $model = new ResendVerificationEmailForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $sent = $model->sendEmail(
                $this->mailer,
                Yii::$app->params['supportEmail'],
                Yii::$app->name,
            );

            if ($sent) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');
                return $this->goHome();
            }

            Yii::$app->session->setFlash('error', 'Sorry, we are unable to resend verification email for the provided email address.');
        }

        return $this->render('resendVerificationEmail', [
            'model' => $model,
        ]);
    }
}

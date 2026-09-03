<?php

declare(strict_types=1);

namespace backend\controllers;

use common\models\LoginForm;
use common\models\Visit;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ErrorAction;
use yii\web\Response;

/**
 * Site controller
 */
class SiteController extends Controller
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
     * Admin / receptionist dashboard with live visit stats.
     *
     * @return string
     */
    public function actionIndex(): string
    {
        $todayStart = date('Y-m-d 00:00:00');

        $totalVisitsToday = (int) Visit::find()
            ->where(['>=', 'check_in_time', $todayStart])
            ->count();

        $currentlyInside = (int) Visit::find()
            ->where(['check_out_time' => null])
            ->count();

        $totalCheckedOut = (int) Visit::find()
            ->where(['not', ['check_out_time' => null]])
            ->count();

        $recentVisits = Visit::find()
            ->with(['visitor', 'host'])
            ->orderBy(['id' => SORT_DESC])
            ->limit(8)
            ->all();

        return $this->render('index', [
            'totalVisitsToday' => $totalVisitsToday,
            'currentlyInside' => $currentlyInside,
            'totalCheckedOut' => $totalCheckedOut,
            'recentVisits' => $recentVisits,
        ]);
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
            return $this->goBack();
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

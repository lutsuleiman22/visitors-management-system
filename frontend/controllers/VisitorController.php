<?php

declare(strict_types=1);

namespace frontend\controllers;

use common\models\Visit;
use common\models\User;
use common\services\AuditLogService;
use common\services\NotificationService;
use frontend\models\CheckInForm;
use frontend\models\CheckOutForm;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Frontend visitor check-in, check-out, and pass.
 */
class VisitorController extends Controller
{
    private function getCheckInSessionKey(): string
    {
        return 'visitor_checkin_data';
    }

    private function getCheckoutSessionKey(): string
    {
        return 'visitor_checkout_data';
    }

    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'check-in' => ['GET', 'POST'],
                    'check-out' => ['GET', 'POST'],
                    'checkout-page' => ['GET', 'POST'],
                    'search-active-visitors' => ['GET'],
                    'do-checkout' => ['POST'],
                    'pass' => ['GET'],
                    'index' => ['GET'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $visits = Visit::find()
            ->with('visitor')
            ->orderBy(['check_in_time' => SORT_DESC])
            ->all();

        return $this->render('index', ['visits' => $visits]);
    }

    /**
     * @return string|Response
     */
    public function actionCheckIn(): string|Response
    {
        $request = Yii::$app->request;
        $step = $request->post('step', 'form');
        $model = new CheckInForm();
        $hosts = CheckInForm::hostList();

        if ($request->isPost) {
            $post = $request->post();
            $model->load($post);

            if ($step === 'preview') {
                if ($model->validate()) {
                    Yii::$app->session->set($this->getCheckInSessionKey(), $model->attributes);
                    return $this->render('check-in', ['model' => $model, 'hosts' => $hosts, 'step' => 'preview']);
                }

                return $this->render('check-in', ['model' => $model, 'hosts' => $hosts, 'step' => 'form']);
            }

            if ($step === 'confirm') {
                $savedData = Yii::$app->session->get($this->getCheckInSessionKey(), []);
                if ($savedData === []) {
                    Yii::$app->session->setFlash('error', 'Your check-in details are missing. Please start again.');
                    return $this->redirect(['check-in']);
                }

                $model = new CheckInForm();
                $model->setAttributes($savedData);
                $visit = $model->process();

                if ($visit !== null) {
                    Yii::$app->session->remove($this->getCheckInSessionKey());
                    AuditLogService::logAction('check-in', 'Visitor checked in through frontend.');
                    NotificationService::createNotification('New visitor checked in.', 'success');
                    Yii::$app->session->setFlash('success', 'Check-in successful. Please print or save your visitor pass.');

                    return $this->render('check-in', ['model' => $model, 'hosts' => $hosts, 'step' => 'success', 'visit' => $visit]);
                }

                Yii::$app->session->setFlash('error', 'Check-in could not be saved. Please review the details and try again.');
                return $this->render('check-in', ['model' => $model, 'hosts' => $hosts, 'step' => 'preview']);
            }
        }

        Yii::$app->session->remove($this->getCheckInSessionKey());
        return $this->render('check-in', ['model' => $model, 'hosts' => $hosts, 'step' => 'form']);
    }

    /**
     * @return string|Response
     */
    public function actionCheckOut(): string|Response
    {
        $request = Yii::$app->request;
        $model = new CheckOutForm();
        $step = $request->post('checkout_step', 'search');
        $selectedVisit = null;

        if ($request->isPost) {
            $model->load($request->post());
            if ($step === 'preview') {
                $visitId = (int) $request->post('visit_id', 0);
                $selectedVisit = $this->findActiveVisitById($visitId);

                if ($selectedVisit === null) {
                    Yii::$app->session->setFlash('error', 'Type the exact checked-in visitor name to continue.');
                    return $this->render('checkout-page', ['model' => $model, 'step' => 'search', 'visit' => null]);
                }

                Yii::$app->session->set($this->getCheckoutSessionKey(), ['visit_id' => $selectedVisit->id]);
                return $this->render('checkout-page', ['model' => $model, 'step' => 'preview', 'visit' => $selectedVisit]);
            }

            if ($step === 'confirm') {
                $savedData = Yii::$app->session->get($this->getCheckoutSessionKey(), []);
                if (empty($savedData)) {
                    Yii::$app->session->setFlash('error', 'The checkout session expired. Please choose the visitor again.');
                    return $this->redirect(['checkout-page']);
                }

                $selectedVisit = $this->findActiveVisitById((int) ($savedData['visit_id'] ?? 0));
                if ($selectedVisit === null) {
                    Yii::$app->session->setFlash('error', 'This visitor is no longer checked in.');
                    return $this->redirect(['checkout-page']);
                }

                if ($selectedVisit->checkOut()) {
                    Yii::$app->session->remove($this->getCheckoutSessionKey());
                    AuditLogService::logAction('check-out', 'Visitor checked out through frontend.');
                    NotificationService::createNotification('Visitor checked out: ' . $selectedVisit->visitor->full_name, 'success');
                    Yii::$app->session->setFlash('success', 'Visitor "' . $selectedVisit->visitor->full_name . '" has been checked out successfully.');

                    return $this->render('checkout-page', ['model' => $model, 'step' => 'success', 'visit' => $selectedVisit]);
                }

                Yii::$app->session->setFlash('error', 'Unable to complete check-out. Please try again.');
                return $this->render('checkout-page', ['model' => $model, 'step' => 'preview', 'visit' => $selectedVisit]);
            }
        }

        Yii::$app->session->remove($this->getCheckoutSessionKey());
        return $this->render('checkout-page', ['model' => $model, 'step' => 'search', 'visit' => $selectedVisit]);
    }

    public function actionCheckoutPage(): string
    {
        return $this->actionCheckOut();
    }

    public function actionSearchActiveVisitors(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $term = trim((string) Yii::$app->request->get('q', ''));
        if ($term === '') {
            return [];
        }

        $visits = Visit::find()
            ->alias('v')
            ->joinWith(['visitor visitor'])
            ->where(['v.status' => Visit::STATUS_CHECKED_IN, 'v.check_out_time' => null])
            ->andWhere([
                'or',
                ['like', 'LOWER(visitor.full_name)', '%' . mb_strtolower($term) . '%', false],
                ['like', 'visitor.phone_number', '%' . $term . '%', false],
            ])
            ->orderBy(['v.check_in_time' => SORT_DESC])
            ->limit(8)
            ->all();

        return array_map(static function (Visit $visit): array {
            return [
                'id' => (int) $visit->id,
                'full_name' => $visit->visitor?->full_name ?? 'Unknown visitor',
                'phone_number' => $visit->visitor?->phone_number ?? '',
                'check_in_time' => $visit->check_in_time ?: '—',
                'visitor_pass_number' => $visit->visitor_pass_number ?? '',
            ];
        }, $visits);
    }

    public function actionDoCheckout(int $id): Response
    {
        $todayStart = date('Y-m-d 00:00:00');
        $tomorrowStart = date('Y-m-d 00:00:00', strtotime('+1 day'));
        $visit = Visit::find()
            ->with(['visitor', 'host'])
            ->where([
                'id' => $id,
                'status' => Visit::STATUS_CHECKED_IN,
                'check_out_time' => null,
            ])
            ->andWhere(['>=', 'check_in_time', $todayStart])
            ->andWhere(['<', 'check_in_time', $tomorrowStart])
            ->one();

        if ($visit !== null && $visit->checkOut()) {
            AuditLogService::logAction('check-out', 'Visitor checked out through today\'s visitor list.');
            NotificationService::createNotification('Visitor checked out: ' . $visit->visitor->full_name, 'success');
            Yii::$app->session->setFlash('success', 'Check-out successful. Thank you, ' . $visit->visitor->full_name . '.');
        } else {
            Yii::$app->session->setFlash('error', 'This visitor is no longer available for check-out.');
        }

        return $this->redirect(['checkout-page']);
    }

    private function findActiveVisitById(int $visitId): ?Visit
    {
        if ($visitId <= 0) {
            return null;
        }

        return Visit::find()
            ->with(['visitor', 'host'])
            ->where(['id' => $visitId, 'status' => Visit::STATUS_CHECKED_IN, 'check_out_time' => null])
            ->one();
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionPass(int $id): string
    {
        $visit = Visit::find()->with(['visitor', 'host'])->where(['id' => $id])->one();
        if ($visit === null) {
            throw new NotFoundHttpException('Visitor pass not found.');
        }

        return $this->render('pass', [
            'visit' => $visit,
        ]);
    }
}
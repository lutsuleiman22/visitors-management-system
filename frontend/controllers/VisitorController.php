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
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'check-in' => ['GET', 'POST'],
                    'check-out' => ['GET', 'POST'],
                    'checkout-page' => ['GET'],
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
        $model = new CheckInForm();

        $post = Yii::$app->request->post();
        if (isset($post['CheckInForm']) && is_array($post['CheckInForm']) && empty($post['CheckInForm']['host_user_id']) && !empty($post['CheckInForm']['host_name'])) {
            $legacyHostName = trim((string) $post['CheckInForm']['host_name']);
            $legacyHost = User::find()->select('id')->where(['username' => $legacyHostName, 'status' => User::STATUS_ACTIVE])->scalar();
            if ($legacyHost !== false && $legacyHost !== null) {
                $post['CheckInForm']['host_user_id'] = (int) $legacyHost;
            }
        }
        $loaded = $post !== [] && $model->load($post);
        if ($post !== [] && !$loaded) {
            $model->addError('full_name', 'The submitted check-in form could not be read. Please refresh and try again.');
        }

        if ($loaded) {
            $visit = $model->process();
            if ($visit !== null) {
                AuditLogService::logAction('check-in', 'Visitor checked in through frontend.');
                NotificationService::createNotification('New visitor checked in.', 'success');
                Yii::$app->session->setFlash(
                    'success',
                    'Check-in successful. Please print or save your visitor pass.',
                );

                return $this->redirect(['pass', 'id' => $visit->id]);
            }

            $errors = $model->getErrorSummary(true);
            Yii::error(['checkInErrors' => $errors, 'attributes' => $model->attributes], __METHOD__);
            Yii::$app->session->setFlash('error', $errors === []
                ? 'Check-in could not be saved. Please review the form and try again.'
                : implode(' ', $errors));
        }

        return $this->render('check-in', [
            'model' => $model,
            'hosts' => CheckInForm::hostList(),
        ]);
    }

    /**
     * @return string|Response
     */
    public function actionCheckOut(): string|Response
    {
        if (Yii::$app->request->getIsGet()) {
            return $this->redirect(['checkout-page']);
        }

        $model = new CheckOutForm();
        $matchedVisit = null;

        if ($model->load(Yii::$app->request->post())) {
            $matchedVisit = $model->findActiveVisit();

            if ($matchedVisit === null && !$model->hasErrors()) {
                $model->addError('qr_code_hash', 'No active checked-in visit found for the provided details.');
            }

            $confirm = (bool) Yii::$app->request->post('confirm_checkout', false);
            if ($matchedVisit !== null && $confirm) {
                if ($matchedVisit->checkOut()) {
                    AuditLogService::logAction('check-out', 'Visitor checked out through frontend.');
                    NotificationService::createNotification('Visitor checked out.', 'success');
                    Yii::$app->session->setFlash(
                        'success',
                        'Visitor "' . $matchedVisit->visitor->full_name . '" has been checked out successfully.',
                    );

                    return $this->refresh();
                }

                Yii::$app->session->setFlash('error', 'Unable to complete check-out. Please try again.');
            }
        }

        return $this->render('check-out', [
            'model' => $model,
            'matchedVisit' => $matchedVisit,
        ]);
    }

    public function actionCheckoutPage(): string
    {
        $todayStart = date('Y-m-d 00:00:00');
        $tomorrowStart = date('Y-m-d 00:00:00', strtotime('+1 day'));
        $visits = Visit::find()
            ->with(['visitor', 'host'])
            ->where(['status' => Visit::STATUS_CHECKED_IN, 'check_out_time' => null])
            ->andWhere(['>=', 'check_in_time', $todayStart])
            ->andWhere(['<', 'check_in_time', $tomorrowStart])
            ->orderBy(['check_in_time' => SORT_DESC])
            ->all();

        return $this->render('checkout-page', ['visits' => $visits]);
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
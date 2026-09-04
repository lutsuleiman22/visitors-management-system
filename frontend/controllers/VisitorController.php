<?php

declare(strict_types=1);

namespace frontend\controllers;

use common\models\Visit;
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

        if ($model->load(Yii::$app->request->post())) {
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

            Yii::$app->session->setFlash('error', implode(' ', $model->getErrorSummary(true)));
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
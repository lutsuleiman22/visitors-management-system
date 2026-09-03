<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\models\VisitSearch;
use common\models\User;
use common\models\Visit;
use common\models\Visitor;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Visit management CRUD and evacuation roster.
 */
class VisitController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'check-out' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $searchModel = new VisitSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Currently on-site visitors (check_out_time IS NULL).
     */
    public function actionEvacuation(): string
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Visit::find()
                ->alias('v')
                ->with(['visitor', 'host'])
                ->where(['v.check_out_time' => null])
                ->orderBy(['v.check_in_time' => SORT_ASC]),
            'pagination' => false,
            'sort' => false,
        ]);

        return $this->render('evacuation', [
            'dataProvider' => $dataProvider,
            'count' => (int) $dataProvider->getTotalCount(),
            'generatedAt' => time(),
        ]);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionView(int $id): string
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * @return string|Response
     */
    public function actionCreate(): string|Response
    {
        $model = new Visit();
        $model->status = Visit::STATUS_CHECKED_IN;
        $model->qr_code_hash = Visit::generateQrCodeHash();
        $model->check_in_time = date('Y-m-d H:i:s');

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Visit created successfully.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'visitors' => $this->visitorList(),
            'hosts' => $this->hostList(),
        ]);
    }

    /**
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate(int $id): string|Response
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Visit updated successfully.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'visitors' => $this->visitorList(),
            'hosts' => $this->hostList(),
        ]);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionDelete(int $id): Response
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Visit deleted.');

        return $this->redirect(['index']);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionCheckOut(int $id): Response
    {
        $model = $this->findModel($id);

        if ($model->checkOut()) {
            Yii::$app->session->setFlash('success', 'Visitor checked out successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Visit is not currently checked in.');
        }

        return $this->redirect(Yii::$app->request->referrer ?: ['view', 'id' => $id]);
    }

    /**
     * @throws NotFoundHttpException
     */
    protected function findModel(int $id): Visit
    {
        $model = Visit::find()->with(['visitor', 'host'])->where(['id' => $id])->one();
        if ($model === null) {
            throw new NotFoundHttpException('The requested visit does not exist.');
        }

        return $model;
    }

    /**
     * @return array<int, string>
     */
    protected function visitorList(): array
    {
        $list = [];
        foreach (Visitor::find()->orderBy(['full_name' => SORT_ASC])->all() as $visitor) {
            $label = $visitor->full_name;
            if ($visitor->national_id) {
                $label .= ' (' . $visitor->national_id . ')';
            }
            $list[$visitor->id] = $label;
        }

        return $list;
    }

    /**
     * @return array<int, string>
     */
    protected function hostList(): array
    {
        return User::find()
            ->select(['username', 'id'])
            ->where(['status' => User::STATUS_ACTIVE])
            ->indexBy('id')
            ->orderBy(['username' => SORT_ASC])
            ->column();
    }
}

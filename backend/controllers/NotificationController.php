<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\BaseController;
use common\models\Notification;
use common\models\User;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Response;

class NotificationController extends BaseController
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'mark-as-read' => ['POST'],
                    'read' => ['POST'],
                ],
            ],
        ]);
    }

    public function actionIndex(): string
    {
        $this->requireRole(User::ROLE_ADMIN, User::ROLE_RECEPTION, User::ROLE_SECURITY);
        return $this->render('index', ['items' => $this->items()]);
    }

    public function actionFeed(): Response
    {
        $this->requireRole(User::ROLE_ADMIN, User::ROLE_RECEPTION, User::ROLE_SECURITY);
        return $this->asJson(['success' => true, 'data' => $this->items(), 'message' => '']);
    }

    public function actionMarkAsRead(int $id): Response
    {
        $this->requireRole(User::ROLE_ADMIN, User::ROLE_RECEPTION, User::ROLE_SECURITY);
        try {
            Notification::updateAll(
                ['is_read' => 1],
                [
                    'and',
                    ['id' => $id],
                    ['or', ['user_id' => (int) Yii::$app->user->id], ['user_id' => null]],
                ],
            );
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->asJson(['success' => false, 'data' => [], 'message' => 'Unable to mark notification as read.']);
        }
        return $this->asJson(['success' => true, 'data' => [], 'message' => 'Notification marked as read.']);
    }

    /** Backward-compatible alias for existing notification links. */
    public function actionRead(int $id): Response
    {
        return $this->actionMarkAsRead($id);
    }

    private function items(): array
    {
        $userId = (int) Yii::$app->user->id;
        try {
            return Notification::find()
                ->alias('n')
                ->select(['n.id', 'n.user_id', 'n.title', 'n.message', 'n.type', 'n.is_read', 'n.created_at'])
                ->where(['or', ['n.user_id' => $userId], ['n.user_id' => null]])
                ->orderBy(['n.created_at' => SORT_DESC, 'n.id' => SORT_DESC])
                ->limit(30)
                ->asArray()
                ->all();
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return [];
        }
    }
}

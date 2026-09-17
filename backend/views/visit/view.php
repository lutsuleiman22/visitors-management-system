<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var common\models\Visit $model */

use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = 'Visit #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Visits', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$identity = Yii::$app->user->identity;
$role = '';
if ($identity !== null) {
    if (method_exists($identity, 'getRole')) {
        $role = (string) $identity->getRole();
    } elseif (array_key_exists('role', $identity->attributes)) {
        $role = (string) $identity->attributes['role'];
    }
}
$role = strtolower(trim($role));
$canEdit = $role === 'admin';
$canDelete = $role === 'admin';
$canCheckOut = in_array($role, ['admin', 'reception'], true);
?>
<div class="visit-view">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 mb-0"><?= Html::encode($this->title) ?></h1>
        <div class="d-flex flex-wrap gap-2">
            <?php if ($canEdit): ?>
                <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?php endif; ?>
            <?php if ($canCheckOut && $model->isCheckedIn()): ?>
                <?= Html::a('Check-Out', ['check-out', 'id' => $model->id], [
                    'class' => 'btn btn-warning',
                    'data' => [
                        'method' => 'post',
                        'confirm' => 'Check out this visitor?',
                    ],
                ]) ?>
            <?php endif; ?>
            <?php if ($canDelete): ?>
                <?= Html::a('Delete', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Are you sure you want to delete this visit?',
                    'method' => 'post',
                ],
                ]) ?>
            <?php endif; ?>
        </div>
    </div>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'visitor_id',
                'value' => $model->visitor->full_name ?? '—',
            ],
            [
                'label' => 'Phone',
                'value' => $model->visitor->phone_number ?? '—',
            ],
            [
                'label' => 'Gender',
                'value' => $model->visitor->gender ?? '—',
            ],
            [
                'label' => 'PBZ Branch',
                'value' => \common\services\BranchCatalog::all()[$model->branch_code] ?? 'Unassigned',
            ],
            [
                'attribute' => 'host_user_id',
                'value' => $model->host->username ?? '—',
            ],
            [
                'attribute' => 'department_code',
                'value' => $model->department_code ?: '—',
            ],
            'from_location',
            'destination',
            [
                'label' => 'Signature',
                'format' => 'raw',
                'value' => $model->signature_path
                    ? Html::a(Html::img(Yii::getAlias('@web/' . ltrim($model->signature_path, '/')), ['alt' => 'Visitor signature', 'style' => 'max-width: 320px; max-height: 120px; border: 1px solid #dce6eb; border-radius: 6px; padding: 4px; background: #fff;']), Yii::getAlias('@web/' . ltrim($model->signature_path, '/')), ['target' => '_blank', 'rel' => 'noopener'])
                    : '—',
            ],
            'qr_code_hash',
            'status',
            'check_in_time',
            [
                'label' => 'Checked In By',
                'value' => $model->checkedInBy?->username ?? 'Unknown / legacy',
            ],
            'check_out_time',
            [
                'label' => 'Checked Out By',
                'value' => $model->checkedOutBy?->username ?? ($model->check_out_time ? 'Unknown / legacy' : '—'),
            ],
            [
                'attribute' => 'created_at',
                'format' => ['datetime', 'php:Y-m-d H:i:s'],
            ],
            [
                'attribute' => 'updated_at',
                'format' => ['datetime', 'php:Y-m-d H:i:s'],
            ],
        ],
    ]) ?>
</div>

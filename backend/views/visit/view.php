<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var common\models\Visit $model */

use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = 'Visit #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Visits', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="visit-view">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 mb-0"><?= Html::encode($this->title) ?></h1>
        <div class="d-flex flex-wrap gap-2">
            <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?php if ($model->isCheckedIn()): ?>
                <?= Html::a('Check-Out', ['check-out', 'id' => $model->id], [
                    'class' => 'btn btn-warning',
                    'data' => [
                        'method' => 'post',
                        'confirm' => 'Check out this visitor?',
                    ],
                ]) ?>
            <?php endif; ?>
            <?= Html::a('Delete', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Are you sure you want to delete this visit?',
                    'method' => 'post',
                ],
            ]) ?>
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
                'label' => 'National ID',
                'value' => $model->visitor->national_id ?? '—',
            ],
            [
                'label' => 'Phone',
                'value' => $model->visitor->phone_number ?? '—',
            ],
            [
                'attribute' => 'host_user_id',
                'value' => $model->host->username ?? '—',
            ],
            'purpose',
            'qr_code_hash',
            'status',
            'check_in_time',
            'check_out_time',
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

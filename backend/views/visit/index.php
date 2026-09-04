<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var backend\models\VisitSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

use common\models\Visit;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Visits';
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
$canCreate = in_array($role, ['admin', 'reception'], true);
$canUpdate = $role === 'admin';
$canDelete = $role === 'admin';
$canCheckOut = in_array($role, ['admin', 'reception'], true);
?>
<div class="visit-index">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 mb-0"><?= Html::encode($this->title) ?></h1>
        <div class="d-flex flex-wrap gap-2">
            <?= Html::a('Evacuation List', ['evacuation'], ['class' => 'btn btn-warning']) ?>
            <?php if ($canCreate): ?>
                <?= Html::a('Create Visit', ['create'], ['class' => 'btn btn-success']) ?>
            <?php endif; ?>
        </div>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions' => ['class' => 'table table-striped table-bordered table-hover align-middle'],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'id',
            [
                'attribute' => 'visitor_name',
                'label' => 'Visitor',
                'value' => static fn (Visit $model): string => $model->visitor->full_name ?? '—',
            ],
            [
                'attribute' => 'visitor_phone',
                'label' => 'Phone',
                'value' => static fn (Visit $model): string => $model->visitor->phone_number ?? '—',
            ],
            [
                'attribute' => 'host_username',
                'label' => 'Host',
                'value' => static fn (Visit $model): string => $model->host->username ?? '—',
            ],
            'purpose',
            'from_location',
            'visitor_pass_number',
            [
                'attribute' => 'status',
                'filter' => Visit::statusList(),
                'format' => 'raw',
                'value' => static function (Visit $model): string {
                    $class = $model->status === Visit::STATUS_CHECKED_IN ? 'success' : 'dark';
                    return Html::tag('span', Html::encode($model->status), [
                        'class' => 'badge text-bg-' . $class,
                    ]);
                },
            ],
            [
                'attribute' => 'active_only',
                'filter' => ['0' => 'All visits', '1' => 'Active only'],
                'value' => static fn (Visit $model): string => $model->isCheckedIn() ? 'Yes' : 'No',
                'contentOptions' => ['class' => 'text-center'],
            ],
            'check_in_time',
            'check_out_time',
            [
                'class' => ActionColumn::class,
                'template' => '{view}' . ($canUpdate ? ' {update}' : '') . ($canCheckOut ? ' {check-out}' : '') . ($canDelete ? ' {delete}' : ''),
                'buttons' => [
                    'check-out' => static function (string $url, Visit $model): string {
                        if (!$model->isCheckedIn()) {
                            return '';
                        }
                        return Html::a('Check-Out', ['check-out', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-outline-danger',
                            'data' => [
                                'method' => 'post',
                                'confirm' => 'Are you sure you want to check out this visitor?',
                            ],
                        ]);
                    },
                ],
            ],
        ],
    ]) ?>
</div>

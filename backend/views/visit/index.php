<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var backend\models\VisitSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array<string, string> $branches */
/** @var string $selectedBranch */

use common\services\BranchCatalog;
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
        <?= Html::beginForm(['index'], 'get', ['class' => 'd-flex flex-wrap gap-2 align-items-center']) ?>
            <?= Html::hiddenInput('branch', $selectedBranch) ?>
            <?= Html::textInput('VisitSearch[visitor_name]', $searchModel->visitor_name, ['class' => 'form-control', 'placeholder' => 'Search visitor by name', 'aria-label' => 'Search visitor by name']) ?>
            <?= Html::submitButton('Search Visit', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Clear', ['index', 'branch' => $selectedBranch], ['class' => 'btn btn-outline-secondary']) ?>
        <?= Html::endForm() ?>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
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
                'label' => 'Gender',
                'value' => static fn (Visit $model): string => $model->visitor->gender ?? '—',
            ],
            [
                'attribute' => 'host_username',
                'label' => 'Host',
                'value' => static fn (Visit $model): string => $model->host->username ?? '—',
            ],
            [
                'attribute' => 'branch_code',
                'label' => 'PBZ Branch',
                'value' => static fn (Visit $model): string => BranchCatalog::all()[$model->branch_code] ?? 'Unassigned',
            ],
            [
                'attribute' => 'department_code',
                'label' => 'Department',
                'value' => static fn (Visit $model): string => $model->department_code ?: '—',
            ],
            [
                'label' => 'Checked In By',
                'value' => static fn (Visit $model): string => $model->checkedInBy?->username ?? 'Unknown / legacy',
            ],
            'purpose',
            'from_location',
            'destination',
            [
                'label' => 'Status',
                'format' => 'raw',
                'value' => static function (Visit $model): string {
                    $isToday = $model->created_at !== null && date('Y-m-d') === date('Y-m-d', (int) $model->created_at);
                    if ($model->check_out_time !== null && $model->check_out_time !== '') {
                        $status = 'Completed';
                        $class = 'primary';
                    } elseif ($model->created_at !== null && !$isToday) {
                        $status = 'Left Without Checkout';
                        $class = 'danger';
                    } else {
                        $status = 'Active';
                        $class = 'success';
                    }

                    return Html::tag('span', Html::encode($status), ['class' => 'badge text-bg-' . $class]);
                },
            ],
            'check_in_time',
            [
                'label' => 'Checked Out By',
                'value' => static fn (Visit $model): string => $model->checkedOutBy?->username ?? ($model->check_out_time ? 'Unknown / legacy' : '—'),
            ],
            [
                'label' => 'Checkout Status',
                'value' => static function (Visit $model): string {
                    if ($model->check_out_time !== null && $model->check_out_time !== '') {
                        return 'Checked out at: ' . $model->check_out_time;
                    }

                    $isToday = $model->created_at !== null && date('Y-m-d') === date('Y-m-d', (int) $model->created_at);

                    return $model->created_at !== null && !$isToday
                        ? 'Missed checkout (system auto-detected)'
                        : 'Still inside';
                },
            ],
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

<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var int $count */
/** @var int $generatedAt */

use common\models\Visit;
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Evacuation List';
$this->params['breadcrumbs'][] = ['label' => 'Visits', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerMetaTag([
    'http-equiv' => 'refresh',
    'content' => '30',
]);
?>
<div class="visit-evacuation">
    <div class="alert alert-danger d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <strong>EVACUATION ROSTER</strong> —
            <span class="fs-4 fw-bold"><?= (int) $count ?></span> visitor(s) currently inside
        </div>
        <div class="small">
            Updated: <?= Yii::$app->formatter->asDatetime($generatedAt) ?>
            (auto-refresh every 30s)
        </div>
    </div>

    <div class="d-print-none d-flex flex-wrap gap-2 mb-3">
        <?= Html::a('Back to Visits', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        <?= Html::a('Refresh Now', ['evacuation'], ['class' => 'btn btn-primary']) ?>
        <button type="button" class="btn btn-dark" onclick="window.print()">Print List</button>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => "{items}\n{summary}",
        'tableOptions' => ['class' => 'table table-bordered table-sm align-middle'],
        'emptyText' => 'No visitors are currently checked in. Building appears clear.',
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'label' => 'Visitor Name',
                'value' => static fn (Visit $model): string => $model->visitor->full_name ?? '—',
            ],
            [
                'label' => 'National ID',
                'value' => static fn (Visit $model): string => $model->visitor->national_id ?? '—',
            ],
            [
                'label' => 'Phone',
                'value' => static fn (Visit $model): string => $model->visitor->phone_number ?? '—',
            ],
            [
                'label' => 'Host',
                'value' => static fn (Visit $model): string => $model->host->username ?? '—',
            ],
            'purpose',
            'check_in_time',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{check-out}',
                'contentOptions' => ['class' => 'd-print-none'],
                'headerOptions' => ['class' => 'd-print-none'],
                'buttons' => [
                    'check-out' => static function ($url, Visit $model): string {
                        return Html::a('Check-Out', ['check-out', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-outline-danger',
                            'data' => [
                                'method' => 'post',
                                'confirm' => 'Mark this visitor as checked out / evacuated?',
                            ],
                        ]);
                    },
                ],
            ],
        ],
    ]) ?>
</div>
<?php
$this->registerCss(<<<'CSS'
@media print {
    header, footer, .d-print-none, .breadcrumb, #theme-toggle { display: none !important; }
    main > .container { padding-top: 10px !important; }
}
CSS);
?>

<?php

declare(strict_types=1);

use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Security Dashboard';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><div class="text-uppercase small fw-semibold text-warning">Safety desk</div><h1 class="h2 mb-1">Security Dashboard</h1><p class="text-body-secondary mb-0">Monitor everyone currently inside and verify identity at exit.</p></div><?= Html::a('Active Visitors', ['/visit/evacuation'], ['class' => 'btn btn-warning']) ?></div>
<div class="alert alert-warning border-0 shadow-sm"><strong><?= count($activeVisits) ?> visitor(s) currently inside.</strong> Verify identity against the record before allowing departure.</div>
<?= GridView::widget([
    'dataProvider' => new yii\data\ArrayDataProvider(['allModels' => $activeVisits, 'pagination' => ['pageSize' => 20]]),
    'tableOptions' => ['class' => 'table table-striped table-hover align-middle'],
    'emptyText' => 'No active visitors currently inside.',
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        ['label' => 'Visitor', 'value' => static fn ($visit): string => $visit->visitor->full_name ?? '—'],
        ['label' => 'Phone', 'value' => static fn ($visit): string => $visit->visitor->phone_number ?? '—'],
        ['label' => 'ID Number', 'value' => static fn ($visit): string => $visit->visitor->national_id ?? '—'],
        ['label' => 'Host', 'value' => static fn ($visit): string => $visit->host->username ?? '—'],
        'purpose', 'check_in_time',
        ['class' => 'yii\grid\ActionColumn', 'template' => '{view}', 'buttons' => ['view' => static fn ($url, $visit): string => Html::a('Verify', ['/visit/view', 'id' => $visit->id], ['class' => 'btn btn-sm btn-outline-primary'])]],
    ],
]) ?>
<h2 class="h5 mt-4 mb-2">Security Logs</h2>
<?= GridView::widget([
    'dataProvider' => new yii\data\ArrayDataProvider(['allModels' => $recentVisits, 'pagination' => ['pageSize' => 10]]),
    'tableOptions' => ['class' => 'table table-sm table-striped align-middle'],
    'columns' => [['label' => 'Visitor', 'value' => static fn ($visit): string => $visit->visitor->full_name ?? '—'], ['attribute' => 'status', 'format' => 'raw', 'value' => static fn ($visit): string => Html::tag('span', Html::encode($visit->status), ['class' => 'badge text-bg-' . ($visit->status === 'Checked-In' ? 'success' : 'secondary')])], 'check_in_time', 'check_out_time'],
]) ?>

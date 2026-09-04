<?php

declare(strict_types=1);

use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Reception Dashboard';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div><div class="text-uppercase small fw-semibold text-success">Front desk</div><h1 class="h2 mb-1">Reception Dashboard</h1><p class="text-body-secondary mb-0">Register arrivals and keep today’s visitor flow moving.</p></div>
    <div class="d-flex gap-2 flex-wrap">
        <?= Html::a('Add Visitor', ['/visit/create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Visitor List', ['/visit/index'], ['class' => 'btn btn-outline-primary']) ?>
    </div>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="small text-body-secondary text-uppercase fw-semibold">Today</div><div class="display-6 fw-bold"><?= count($todayVisits) ?></div><div class="text-body-secondary">visitor entries</div></div></div></div>
    <div class="col-md-4"><div class="card border-0 shadow-sm h-100 border-start border-4 border-success"><div class="card-body"><div class="small text-body-secondary text-uppercase fw-semibold">Operations</div><div class="d-flex gap-2 mt-3"><?= Html::a('Check-In', ['/visit/create'], ['class' => 'btn btn-success btn-sm']) ?><?= Html::a('Check-Out', ['/visit/evacuation'], ['class' => 'btn btn-warning btn-sm']) ?></div></div></div></div>
    <div class="col-md-4"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="small text-body-secondary text-uppercase fw-semibold">Workflow</div><div class="mt-3"><span class="badge text-bg-success">Front desk active</span></div></div></div></div>
</div>
<div class="d-flex justify-content-between align-items-center mb-2"><h2 class="h5 mb-0">Today’s Visitors</h2><span class="badge text-bg-light border text-body-secondary"><?= count($todayVisits) ?> records</span></div>
<?= GridView::widget([
    'dataProvider' => new yii\data\ArrayDataProvider(['allModels' => $todayVisits, 'pagination' => ['pageSize' => 20]]),
    'tableOptions' => ['class' => 'table table-striped table-hover align-middle'],
    'emptyText' => 'No visitor entries recorded today.',
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        ['label' => 'Visitor', 'value' => static fn ($visit): string => $visit->visitor->full_name ?? '—'],
        ['label' => 'Phone', 'value' => static fn ($visit): string => $visit->visitor->phone_number ?? '—'],
        'purpose', ['attribute' => 'status', 'format' => 'raw', 'value' => static function ($visit): string { $active = $visit->isCheckedIn(); return Html::tag('span', $active ? 'Checked-In' : 'Checked-Out', ['class' => 'badge text-bg-' . ($active ? 'success' : 'secondary')]); }], 'check_in_time', 'check_out_time',
    ],
]) ?>

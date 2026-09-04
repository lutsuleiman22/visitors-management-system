<?php

declare(strict_types=1);

use yii\helpers\Html;

$this->title = 'Live Analytics';
$this->params['breadcrumbs'][] = $this->title;
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js', ['position' => yii\web\View::POS_HEAD]);
?>
<h1 class="h3 mb-4">Live Dashboard Analytics</h1>
<div class="row g-3 mb-4">
    <?php foreach ([['Visitors Today', $today], ['Currently Inside', $inside], ['Checked Out', $checkedOut], ['Pending', $pending]] as [$label, $value]): ?>
        <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><div class="small text-body-secondary"><?= Html::encode($label) ?></div><div class="display-6 fw-bold"><?= (int) $value ?></div></div></div></div>
    <?php endforeach; ?>
</div>
<div class="row g-4"><div class="col-lg-8"><div class="card shadow-sm"><div class="card-body"><canvas id="traffic-chart" height="120"></canvas></div></div></div><div class="col-lg-4"><div class="card shadow-sm"><div class="card-body"><canvas id="roles-chart"></canvas></div></div></div></div>
<?php $this->registerJs(<<<'JS'
fetch('chart-data').then(function (response) { return response.json(); }).then(function (data) {
fetch('chart-data').then(function (response) { return response.json(); }).then(function (response) {
    if (!response.success) return;
    const data = response.data;
    new Chart(document.getElementById('traffic-chart'), { type: 'line', data: { labels: data.hours.map(function (row) { return row.hour + ':00'; }), datasets: [{ label: 'Visits', data: data.hours.map(function (row) { return row.count; }), borderColor: '#0d6efd', backgroundColor: 'rgba(13,110,253,.12)', fill: true, tension: .3 }] }, options: { responsive: true, plugins: { legend: { display: false } } } });
    new Chart(document.getElementById('roles-chart'), { type: 'doughnut', data: { labels: data.roles.map(function (row) { return row.role; }), datasets: [{ data: data.roles.map(function (row) { return row.count; }) }] }, options: { responsive: true } });
});
JS); ?>

<?php

declare(strict_types=1);

/** @var string[] $trendLabels */
/** @var int[] $trendValues */
/** @var string[] $hostLabels */
/** @var int[] $hostValues */

use yii\helpers\Json;

$trendLabelsJson = Json::htmlEncode($trendLabels);
$trendValuesJson = Json::htmlEncode(array_map('intval', $trendValues));
$hostLabelsJson = Json::htmlEncode($hostLabels);
$hostValuesJson = Json::htmlEncode(array_map('intval', $hostValues));

$this->title = 'Analytics';
?>
<div class="analytics-charts">
    <div class="analytics-chart-card"><canvas id="visitor-trend-chart"></canvas></div>
    <div class="analytics-chart-card"><canvas id="host-performance-chart"></canvas></div>
</div>
<?php
$this->registerCss(<<<'CSS'
.analytics-charts { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
.analytics-chart-card { background: #fff; border: 1px solid #eee; min-height: 360px; padding: 1rem; position: relative; }
@media (max-width: 767.98px) { .analytics-charts { grid-template-columns: 1fr; } }
CSS);
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js', ['position' => yii\web\View::POS_HEAD]);
$this->registerJs(<<<JS
(function () {
    if (typeof Chart === 'undefined') return;
    const colors = { blue: '#0d6efd', yellow: '#fff200', ink: '#1a1a1a', grid: '#eeeeee' };
    const common = { responsive: true, maintainAspectRatio: false, animation: false };
    new Chart(document.getElementById('visitor-trend-chart'), {
        type: 'line',
        data: {
            labels: {$trendLabelsJson},
            datasets: [{
                data: {$trendValuesJson},
                borderColor: colors.blue,
                backgroundColor: 'rgba(13,110,253,.08)',
                fill: true,
                tension: .25,
                pointRadius: 2,
                pointBackgroundColor: colors.blue,
            }],
        },
        options: {
            ...common,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: colors.grid } },
            },
        },
    });
    new Chart(document.getElementById('host-performance-chart'), {
        type: 'bar',
        data: {
            labels: {$hostLabelsJson},
            datasets: [{ data: {$hostValuesJson}, backgroundColor: colors.yellow, borderColor: colors.ink, borderWidth: 1 }],
        },
        options: {
            ...common,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: colors.grid } },
            },
        },
    });
}());
JS);
?>

<?php

declare(strict_types=1);

/** @var string[] $trendLabels */
/** @var int[] $trendValues */
/** @var string[] $hostLabels */
/** @var int[] $hostValues */
/** @var string $range */

$range = in_array($range, ['weekly', 'monthly'], true) ? $range : 'weekly';

$trendLabelsJson = json_encode(array_values($trendLabels), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?: '[]';
$trendValuesJson = json_encode(array_map('intval', array_values($trendValues)), JSON_THROW_ON_ERROR);
$hostLabelsJson = json_encode(array_values($hostLabels), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?: '[]';
$hostValuesJson = json_encode(array_map('intval', array_values($hostValues)), JSON_THROW_ON_ERROR);

$this->title = 'Analytics';
?>
<form class="analytics-filter" method="get" action="<?= htmlspecialchars(Yii::$app->urlManager->createUrl(['/analytics/index']), ENT_QUOTES, 'UTF-8') ?>">
    <label for="analytics-range">Range</label>
    <select id="analytics-range" name="range" onchange="this.form.submit()">
        <option value="weekly"<?= $range === 'weekly' ? ' selected' : '' ?>>Weekly</option>
        <option value="monthly"<?= $range === 'monthly' ? ' selected' : '' ?>>Monthly</option>
    </select>
</form>
<div class="analytics-charts">
    <div class="analytics-chart-card"><canvas id="visitor-trend-chart"></canvas></div>
    <div class="analytics-chart-card"><canvas id="host-performance-chart"></canvas></div>
</div>
<?php
$this->registerCss(<<<'CSS'
.analytics-charts { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
.analytics-chart-card { background: #fff; border: 1px solid #eee; min-height: 360px; padding: 1rem; position: relative; }
.analytics-filter { align-items: center; display: flex; gap: .6rem; justify-content: flex-end; margin-bottom: 1rem; }
.analytics-filter label { color: #555; font-size: .8rem; font-weight: 700; }
.analytics-filter select { background: #fff; border: 1px solid #eee; padding: .45rem .7rem; }
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

<?php

declare(strict_types=1);

use yii\helpers\Html;

$this->title = 'Reports & Analytics';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="reports-page">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <div class="reports-eyebrow">Management intelligence</div>
            <h1 class="h2 mb-1">Reports & Analytics</h1>
            <p class="text-body-secondary mb-0">A clear operational view of visitor activity.</p>
        </div>
        <span class="reports-period">Last 7 days</span>
    </div>

    <div class="row g-3 mb-4">
        <?php foreach ([
            ['Total Visitors', $totalVisitors, 'reports-icon reports-icon--yellow', 'VIS'],
            ['Currently Inside', $checkedIn, 'reports-icon reports-icon--blue', 'IN'],
            ['Checked Out', $checkedOut, 'reports-icon reports-icon--green', 'OUT'],
            ['Notifications', $notificationCount, 'reports-icon reports-icon--ink', 'N'],
        ] as [$label, $value, $iconClass, $icon]): ?>
            <div class="col-sm-6 col-xl-3">
                <div class="reports-kpi h-100">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <span class="<?= $iconClass ?>"><?= Html::encode($icon) ?></span>
                        <span class="reports-kpi-label"><?= Html::encode($label) ?></span>
                    </div>
                    <div class="reports-kpi-value"><?= (int) $value ?></div>
                    <div class="reports-kpi-meta">Updated from current records</div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-8">
            <section class="reports-panel h-100">
                <div class="reports-panel-heading"><div><h2>Visitor trends</h2><p>Daily check-ins over the last seven days</p></div><span class="reports-panel-mark">01</span></div>
                <div class="reports-chart reports-chart--line"><canvas id="visitor-trends-chart"></canvas></div>
            </section>
        </div>
        <div class="col-xl-4">
            <section class="reports-panel h-100">
                <div class="reports-panel-heading"><div><h2>Status distribution</h2><p>Current visit state</p></div><span class="reports-panel-mark">02</span></div>
                <div class="reports-chart reports-chart--pie"><canvas id="status-distribution-chart"></canvas></div>
            </section>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-5">
            <section class="reports-panel h-100">
                <div class="reports-panel-heading"><div><h2>Activity overview</h2><p>Visitors grouped by host</p></div><span class="reports-panel-mark">03</span></div>
                <div class="reports-chart reports-chart--bar"><canvas id="host-activity-chart"></canvas></div>
            </section>
        </div>
        <div class="col-xl-7">
            <section class="reports-panel h-100">
                <div class="reports-panel-heading"><div><h2>Recent activity</h2><p>Latest visitor records</p></div><?= Html::a('View all visitors', ['/visit/index'], ['class' => 'reports-link']) ?></div>
                <div class="table-responsive">
                    <table class="table reports-table align-middle mb-0">
                        <thead><tr><th>Name</th><th>Host</th><th>Status</th><th>Check-in time</th></tr></thead>
                        <tbody>
                        <?php if ($recentVisits === []): ?>
                            <tr><td colspan="4" class="text-center text-body-secondary py-4">No visitor activity recorded.</td></tr>
                        <?php else: foreach ($recentVisits as $visit): ?>
                            <?php $isInside = $visit->isCheckedIn(); ?>
                            <tr>
                                <td><strong><?= Html::encode($visit->visitor?->full_name ?? 'Unknown visitor') ?></strong></td>
                                <td><?= Html::encode($visit->host?->username ?? 'Unassigned') ?></td>
                                <td><span class="reports-status reports-status--<?= $isInside ? 'inside' : 'out' ?>"><?= $isInside ? 'Inside' : 'Checked out' ?></span></td>
                                <td class="text-body-secondary"><?= Html::encode($visit->check_in_time ?: '—') ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</div>
<?php
$this->registerCss(<<<'CSS'
.reports-page { --reports-yellow: #fff200; --reports-ink: #1a1a1a; --reports-blue: #0d6efd; --reports-border: #eeeeee; color: var(--reports-ink); }
.reports-eyebrow { color: #6b6b6b; font-size: .72rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
.reports-period { border: 1px solid var(--reports-border); color: #555; font-size: .78rem; font-weight: 700; padding: .55rem .8rem; }
.reports-kpi, .reports-panel { background: #fff; border: 1px solid var(--reports-border); }
.reports-kpi { padding: 1.15rem; }
.reports-kpi-label { color: #6b6b6b; font-size: .75rem; font-weight: 800; letter-spacing: .04em; text-align: right; text-transform: uppercase; }
.reports-kpi-value { font-size: 2.25rem; font-weight: 800; line-height: 1; margin-top: 1.35rem; }
.reports-kpi-meta { color: #8a8a8a; font-size: .75rem; margin-top: .55rem; }
.reports-icon { align-items: center; display: inline-flex; font-size: .65rem; font-weight: 900; height: 2.1rem; justify-content: center; letter-spacing: .02em; width: 2.1rem; }
.reports-icon--yellow { background: var(--reports-yellow); color: var(--reports-ink); }
.reports-icon--blue { background: #e7f0ff; color: var(--reports-blue); }
.reports-icon--green { background: #e8f5ee; color: #167345; }
.reports-icon--ink { background: #edf0f2; color: var(--reports-ink); }
.reports-panel-heading { align-items: flex-start; display: flex; justify-content: space-between; padding: 1.15rem 1.15rem .5rem; }
.reports-panel-heading h2 { font-size: 1rem; font-weight: 800; margin: 0; }
.reports-panel-heading p { color: #858585; font-size: .78rem; margin: .25rem 0 0; }
.reports-panel-mark { color: #aaa; font-size: .7rem; font-weight: 800; letter-spacing: .08em; }
.reports-chart { padding: .75rem 1rem 1rem; position: relative; }
.reports-chart--line { height: 245px; }
.reports-chart--pie { height: 245px; }
.reports-chart--bar { height: 245px; }
.reports-link { color: var(--reports-blue); font-size: .78rem; font-weight: 700; text-decoration: none; }
.reports-table { font-size: .8rem; }
.reports-table thead th { background: #fafafa; border-bottom: 1px solid var(--reports-border); color: #777; font-size: .68rem; letter-spacing: .06em; padding: .7rem 1.15rem; text-transform: uppercase; }
.reports-table tbody td { border-color: var(--reports-border); padding: .75rem 1.15rem; }
.reports-status { display: inline-block; font-size: .7rem; font-weight: 800; padding: .3rem .5rem; }
.reports-status--inside { background: #e8f5ee; color: #167345; }
.reports-status--out { background: #edf0f2; color: #555; }
CSS);
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js', ['position' => yii\web\View::POS_HEAD]);
$trendLabelsJson = json_encode($trendLabels, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?: '[]';
$trendValuesJson = json_encode(array_map('intval', $trendValues), JSON_THROW_ON_ERROR);
$hostLabelsJson = json_encode($hostLabels, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?: '[]';
$hostValuesJson = json_encode(array_map('intval', $hostValues), JSON_THROW_ON_ERROR);
$this->registerJs(<<<JS
(function () {
    if (typeof Chart === 'undefined') return;
    const colors = { blue: '#0d6efd', yellow: '#fff200', green: '#198754', ink: '#1a1a1a', grid: '#eeeeee' };
    const common = { responsive: true, maintainAspectRatio: false, animation: false, plugins: { legend: { display: false } } };
    new Chart(document.getElementById('visitor-trends-chart'), { type: 'line', data: { labels: {$trendLabelsJson}, datasets: [{ data: {$trendValuesJson}, borderColor: colors.blue, backgroundColor: 'rgba(13,110,253,.08)', fill: true, tension: .25, pointRadius: 3, pointBackgroundColor: colors.blue }] }, options: { ...common, scales: { x: { grid: { display: false } }, y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: colors.grid } } } } });
    new Chart(document.getElementById('status-distribution-chart'), { type: 'doughnut', data: { labels: ['Inside', 'Checked out'], datasets: [{ data: [<?= (int) $checkedIn ?>, <?= (int) $checkedOut ?>], backgroundColor: [colors.yellow, colors.blue], borderWidth: 0 }] }, options: { ...common, cutout: '68%', plugins: { legend: { display: true, position: 'bottom', labels: { usePointStyle: true, padding: 18 } } } } });
    new Chart(document.getElementById('host-activity-chart'), { type: 'bar', data: { labels: {$hostLabelsJson}, datasets: [{ data: {$hostValuesJson}, backgroundColor: colors.ink, borderRadius: 0, barThickness: 18 }] }, options: { ...common, indexAxis: 'y', scales: { x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: colors.grid } }, y: { grid: { display: false } } } } });
}());
JS);
?>

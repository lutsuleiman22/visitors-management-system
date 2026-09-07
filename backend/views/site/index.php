<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var int $totalVisitsToday */
/** @var int $currentlyInside */
/** @var int $totalCheckedOut */
/** @var common\models\Visit[] $recentVisits */

use common\models\Notification;
use common\models\Visit;
use common\models\Visitor;
use common\services\NotificationService;
use yii\helpers\Html;
use yii\helpers\Json;

$this->title = 'Dashboard';
$identity = Yii::$app->user->identity;
$username = $identity?->username ?? 'operator';
$role = strtolower((string) ($identity?->role ?? ''));
$roleLabel = strtoupper($role ?: 'operator');
$totalVisitors = 0;
$unreadNotifications = 0;

try {
    $totalVisitors = (int) Visitor::find()->count();
    $unreadNotifications = NotificationService::unreadCount((int) Yii::$app->user->id);
} catch (Throwable $exception) {
    Yii::error($exception->getMessage(), __METHOD__);
}

$statusCounts = ['Active' => 0, 'Checked out' => 0, 'Pending' => 0];
$dailyCounts = [];
foreach ($recentVisits as $visit) {
    $status = $visit->isCheckedIn() ? 'Active' : ($visit->status === Visit::STATUS_CHECKED_OUT ? 'Checked out' : 'Pending');
    $statusCounts[$status]++;
    $day = $visit->check_in_time ? date('M j', strtotime($visit->check_in_time)) : 'Unknown';
    $dailyCounts[$day] = ($dailyCounts[$day] ?? 0) + 1;
}
?>
<div class="site-index">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div><div class="text-uppercase small fw-semibold text-primary">Operations overview</div><h1 class="h2 mb-1">Good day, <?= Html::encode($username) ?></h1><p class="text-body-secondary mb-0">A live snapshot of visitor activity across your site.</p></div>
        <span class="badge rounded-pill text-bg-light border px-3 py-2"><?= Html::encode($roleLabel) ?> access</span>
    </div>
    <div class="row g-3 mb-4">
        <?php foreach ([['Total Visitors', $totalVisitors, 'primary', 'Registered visitor records', 'V'], ['Active Visits', $currentlyInside, 'success', 'Checked in, not checked out', 'A'], ['Today Check-ins', $totalVisitsToday, 'info', 'Entries since midnight', 'T'], ['Unread Notifications', $unreadNotifications, 'warning', 'Requires your attention', 'N']] as [$label, $value, $color, $hint, $icon]): ?>
            <div class="col-6 col-xl-3"><div class="card dashboard-stat-card h-100 border-0 shadow-sm border-start border-4 border-<?= $color ?>"><div class="card-body p-3 p-lg-4"><div class="d-flex justify-content-between align-items-start gap-2"><span class="dashboard-stat-icon text-bg-<?= $color ?>"><?= Html::encode($icon) ?></span><span class="small text-uppercase fw-semibold text-body-secondary text-end"><?= Html::encode($label) ?></span></div><div class="display-6 fw-bold mt-3"><?= (int) $value ?></div><div class="small text-body-secondary"><?= Html::encode($hint) ?></div></div></div></div>
        <?php endforeach; ?>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-xl-8"><div class="card border-0 shadow-sm h-100"><div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center py-3"><div><h2 class="h5 mb-1">Daily visitor trend</h2><p class="small text-body-secondary mb-0">Recent check-in activity</p></div><span class="badge rounded-pill text-bg-light border">Live view</span></div><div class="card-body pt-0"><div class="dashboard-chart-wrap"><canvas id="daily-visitors-chart"></canvas></div></div></div></div>
        <div class="col-xl-4"><div class="card border-0 shadow-sm h-100"><div class="card-header bg-transparent border-0 py-3"><h2 class="h5 mb-1">Visit status</h2><p class="small text-body-secondary mb-0">Current activity breakdown</p></div><div class="card-body pt-0"><div class="dashboard-chart-wrap dashboard-chart-wrap--compact"><canvas id="visit-status-chart"></canvas></div></div></div></div>
    </div>
    <div class="card border-0 shadow-sm"><div class="card-header bg-transparent border-0 d-flex flex-wrap justify-content-between align-items-center gap-2 py-3"><div><h2 class="h5 mb-1">Recent activity</h2><p class="small text-body-secondary mb-0">Latest visitor entries and departures</p></div><?php if ($role === 'admin' || $role === 'reception'): ?><?= Html::a('View visitor list', ['/visit/index'], ['class' => 'btn btn-sm btn-outline-primary']) ?><?php endif; ?></div><div class="table-responsive"><table class="table table-striped table-hover align-middle mb-0"><thead><tr><th>Visitor</th><th>Purpose</th><th>Check-in time</th><th>Status</th></tr></thead><tbody><?php if ($recentVisits === []): ?><tr><td colspan="4" class="text-center text-body-secondary py-5">No recent visitor activity.</td></tr><?php else: foreach (array_slice($recentVisits, 0, 20) as $visit): ?><?php $status = $visit->isCheckedIn() ? 'Active' : ($visit->status === Visit::STATUS_CHECKED_OUT ? 'Checked out' : 'Pending'); $statusColor = $status === 'Active' ? 'success' : ($status === 'Checked out' ? 'primary' : 'warning'); ?><tr><td><strong><?= Html::encode($visit->visitor->full_name ?? 'Unknown visitor') ?></strong><small class="d-block text-body-secondary"><?= Html::encode($visit->visitor->phone_number ?? '') ?></small></td><td><?= Html::encode($visit->purpose ?: '—') ?></td><td><?= Html::encode($visit->check_in_time ?: '—') ?></td><td><span class="badge rounded-pill text-bg-<?= $statusColor ?>"><?= Html::encode($status) ?></span></td></tr><?php endforeach; endif; ?></tbody></table></div></div>
</div>
<?php
$this->registerCss(<<<'CSS'
.dashboard-stat-card { min-height: 154px; }
.dashboard-stat-icon { display: grid; place-items: center; width: 2rem; height: 2rem; border-radius: .55rem; font-size: .78rem; font-weight: 800; }
.dashboard-chart-wrap { position: relative; height: 260px; }
.dashboard-chart-wrap--compact { height: 260px; }
CSS);
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js', ['position' => yii\web\View::POS_HEAD]);
$this->registerJs('if (typeof Chart !== "undefined") { new Chart(document.getElementById("daily-visitors-chart"), { type: "line", data: { labels: ' . Json::htmlEncode(array_keys($dailyCounts)) . ', datasets: [{ label: "Visitors", data: ' . Json::htmlEncode(array_values($dailyCounts)) . ', borderColor: "#0d6efd", backgroundColor: "rgba(13,110,253,.12)", fill: true, tension: .3 }] }, options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } }); new Chart(document.getElementById("visit-status-chart"), { type: "doughnut", data: { labels: ' . Json::htmlEncode(array_keys($statusCounts)) . ', datasets: [{ data: ' . Json::htmlEncode(array_values($statusCounts)) . ', backgroundColor: ["#198754", "#0d6efd", "#f0ad4e"], borderWidth: 0 }] }, options: { maintainAspectRatio: false, plugins: { legend: { position: "bottom" } } } }); }');
?>

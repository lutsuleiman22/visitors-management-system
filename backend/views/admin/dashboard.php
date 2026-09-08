<?php

declare(strict_types=1);

use common\models\Visit;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

$this->title = 'Admin Dashboard';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <div class="text-uppercase small fw-semibold text-primary">Control centre</div>
        <h1 class="h2 mb-1">Admin Dashboard</h1>
        <p class="text-body-secondary mb-0">System overview and operational activity.</p>
    </div>
    <div class="d-flex gap-2">
        <?= Html::a('Manage Users', ['/user/index'], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Manage Visits', ['/visit/index'], ['class' => 'btn btn-outline-primary']) ?>
    </div>
</div>
<div class="row g-3 mb-4">
    <?php foreach ([['Users', $totalUsers, 'primary', null], ['Visitors', $totalVisitors, 'info', null], ['Visits', $totalVisits, 'dark', 'live-total'], ['Currently Inside', $activeVisits, 'success', 'live-inside']] as [$label, $value, $color, $id]): ?>
        <div class="col-sm-6 col-xl-3"><div class="card h-100 border-0 shadow-sm border-start border-4 border-<?= $color ?>"><div class="card-body"><div class="text-body-secondary small text-uppercase fw-semibold"><?= Html::encode($label) ?></div><div class="display-6 fw-bold mt-2"<?= $id === null ? '' : ' id="' . $id . '"' ?>><?= (int) $value ?></div></div></div></div>
    <?php endforeach; ?>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h2 class="h5 mb-3">System Overview</h2>
        <div class="row g-3">
            <div class="col-md-4"><div class="bg-body-tertiary rounded-3 p-3"><div class="small text-body-secondary">Active visitors</div><strong id="live-inside-summary"><?= (int) $activeVisits ?> currently on site</strong></div></div>
            <div class="col-md-4"><div class="bg-body-tertiary rounded-3 p-3"><div class="small text-body-secondary">Visit activity</div><strong id="live-checked-out"><?= (int) $totalVisits - (int) $activeVisits ?> checked out</strong></div></div>
            <div class="col-md-4"><div class="bg-body-tertiary rounded-3 p-3"><div class="small text-body-secondary">Today</div><strong id="live-today"><?= (int) Visit::find()->where(['>=', 'check_in_time', date('Y-m-d 00:00:00')])->count() ?> check-ins today</strong></div></div>
        </div>
    </div>
</div>
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center py-3">
        <div><h2 class="h5 mb-1">Live Activity</h2><p class="small text-body-secondary mb-0">Latest system events</p></div>
        <span class="badge rounded-pill text-bg-success">Live</span>
    </div>
    <div id="live-activity" class="list-group list-group-flush"><div class="list-group-item text-body-secondary">Loading activity...</div></div>
</div>
<?php
$statsUrl = Url::to(['/admin/stats']);
$activityUrl = Url::to(['/admin/activity']);
$statsUrlJson = Json::htmlEncode($statsUrl);
$activityUrlJson = Json::htmlEncode($activityUrl);
$this->registerJs(<<<JS
(function () {
    const statsUrl = {$statsUrlJson};
    const activityUrl = {$activityUrlJson};
    const setText = (id, value) => {
        const element = document.getElementById(id);
        if (element) element.textContent = value;
    };
    const refreshStats = () => fetch(statsUrl, { headers: { Accept: 'application/json' } })
        .then(response => response.ok ? response.json() : Promise.reject(response))
        .then(data => {
            setText('live-total', data.total);
            setText('live-inside', data.inside);
            setText('live-inside-summary', data.inside + ' currently on site');
            setText('live-checked-out', data.checked_out + ' checked out');
            setText('live-today', data.today + ' check-ins today');
        })
        .catch(() => {});
    const refreshActivity = () => fetch(activityUrl, { headers: { Accept: 'application/json' } })
        .then(response => response.ok ? response.json() : Promise.reject(response))
        .then(items => {
            const container = document.getElementById('live-activity');
            if (!container) return;
            container.replaceChildren();
            if (!items.length) {
                const empty = document.createElement('div');
                empty.className = 'list-group-item text-body-secondary';
                empty.textContent = 'No activity recorded yet.';
                container.appendChild(empty);
                return;
            }
            items.forEach(item => {
                const row = document.createElement('div');
                row.className = 'list-group-item d-flex justify-content-between align-items-start gap-3';
                const detail = document.createElement('div');
                const action = document.createElement('strong');
                action.textContent = item.action;
                detail.append(action, document.createTextNode(' - ' + item.description));
                const time = document.createElement('small');
                time.className = 'text-body-secondary text-nowrap';
                time.textContent = item.time;
                row.append(detail, time);
                container.appendChild(row);
            });
        })
        .catch(() => {});
    refreshStats();
    refreshActivity();
    window.setInterval(refreshStats, 5000);
    window.setInterval(refreshActivity, 5000);
}());
JS);
?>

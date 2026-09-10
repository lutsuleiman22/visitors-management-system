<?php

declare(strict_types=1);

use yii\helpers\Html;

$this->title = 'Admin Dashboard';
$this->params['breadcrumbs'][] = $this->title;
$todayLabel = date('M j, Y');
?>
<div class="admin-dashboard">
    <div class="dashboard-heading">
        <div>
            <span class="dashboard-eyebrow">Operations control centre</span>
            <h1>Admin Dashboard</h1>
            <p>Monitor visitor movement and front-desk activity in real time.</p>
        </div>
        <div class="dashboard-actions">
            <span class="dashboard-date"><?= Html::encode($todayLabel) ?></span>
            <?= Html::a('Manage Users', ['/user/index'], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Manage Visits', ['/visit/index'], ['class' => 'btn btn-outline-primary']) ?>
        </div>
    </div>
    <div class="dashboard-stats">
        <?php foreach ([
            ['Total Visitors', $totalVisitors, 'blue', 'V', '+' . (int) $todayVisits . ' today'],
            ['Currently Inside', $activeVisits, 'green', 'IN', 'On site now'],
            ['Checked Out', $checkedOutVisits, 'red', 'OUT', 'Completed visits'],
            ['Pending', $pendingVisits, 'yellow', 'P', 'Needs attention'],
        ] as [$label, $value, $color, $icon, $trend]): ?>
            <article class="dashboard-stat dashboard-stat--<?= $color ?>">
                <div class="dashboard-stat-top"><span class="dashboard-stat-icon"><?= Html::encode($icon) ?></span><span class="dashboard-stat-trend"><?= Html::encode($trend) ?></span></div>
                <div class="dashboard-stat-label"><?= Html::encode($label) ?></div>
                <div class="dashboard-stat-value"><?= (int) $value ?></div>
            </article>
        <?php endforeach; ?>
    </div>
    <div class="dashboard-grid">
        <section class="dashboard-panel dashboard-panel--activity">
            <div class="dashboard-panel-heading"><div><span class="dashboard-eyebrow">Latest movement</span><h2>Live Activity</h2></div><span class="dashboard-live"><span></span> Live</span></div>
            <div class="live-activity">
                <?php if ($recentVisitors === []): ?>
                    <div class="activity-empty">No visitor activity recorded yet.</div>
                <?php else: foreach ($recentVisitors as $visit): ?>
                    <?php $inside = $visit->isCheckedIn(); ?>
                    <div class="activity-row">
                        <div class="activity-avatar"><?= Html::encode(strtoupper(substr((string) ($visit->visitor?->full_name ?? '?'), 0, 1))) ?></div>
                        <div class="activity-details"><strong><?= Html::encode($visit->visitor?->full_name ?? 'Unknown visitor') ?></strong><span><?= Html::encode($visit->host?->username ?? 'Unassigned host') ?></span></div>
                        <div class="activity-meta"><span class="activity-badge activity-badge--<?= $inside ? 'in' : 'out' ?>"><?= $inside ? 'IN' : 'OUT' ?></span><small><?= Html::encode($visit->check_in_time ? date('H:i', strtotime($visit->check_in_time)) : '—') ?></small></div>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </section>
        <section class="dashboard-panel dashboard-panel--snapshot">
            <div class="dashboard-panel-heading"><div><span class="dashboard-eyebrow">Daily pulse</span><h2>Today at a glance</h2></div></div>
            <div class="snapshot-value"><?= (int) $todayVisits ?></div>
            <p>check-ins recorded today</p>
            <div class="snapshot-bar"><span style="width: <?= $totalVisits > 0 ? min(100, round(($todayVisits / $totalVisits) * 100)) : 0 ?>%"></span></div>
            <div class="snapshot-footer"><span>Total visits</span><strong><?= (int) $totalVisits ?></strong></div>
            <div class="snapshot-footer"><span>Visitors inside</span><strong><?= (int) $activeVisits ?></strong></div>
        </section>
    </div>
</div>
<?php $this->registerCss(<<<'CSS'
.admin-dashboard { --dashboard-ink: #17212b; --dashboard-muted: #71808d; --dashboard-border: #e6ebef; --dashboard-blue: #0d6efd; --dashboard-green: #16835a; --dashboard-red: #d9534f; --dashboard-yellow: #fff200; color: var(--dashboard-ink); }
.dashboard-heading { align-items: flex-end; display: flex; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem; }
.dashboard-eyebrow { color: var(--dashboard-muted); display: block; font-size: .68rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
.dashboard-heading h1 { font-size: clamp(1.65rem, 3vw, 2.35rem); font-weight: 800; margin: .25rem 0 .35rem; }
.dashboard-heading p { color: var(--dashboard-muted); margin: 0; }
.dashboard-actions { align-items: center; display: flex; flex-wrap: wrap; gap: .55rem; justify-content: flex-end; }
.dashboard-date { color: var(--dashboard-muted); font-size: .78rem; font-weight: 700; margin-right: .35rem; }
.dashboard-stats { display: grid; gap: .85rem; grid-template-columns: repeat(4, minmax(0, 1fr)); margin-bottom: 1rem; }
.dashboard-stat { background: #fff; border: 1px solid var(--dashboard-border); border-top: 3px solid var(--stat-color); min-height: 154px; padding: 1.1rem 1.15rem; }
.dashboard-stat--blue { --stat-color: var(--dashboard-blue); }.dashboard-stat--green { --stat-color: var(--dashboard-green); }.dashboard-stat--red { --stat-color: var(--dashboard-red); }.dashboard-stat--yellow { --stat-color: #d3bb00; }
.dashboard-stat-top { align-items: center; display: flex; justify-content: space-between; }
.dashboard-stat-icon { align-items: center; background: #eef3f7; color: var(--stat-color); display: inline-flex; font-size: .7rem; font-weight: 900; height: 2.25rem; justify-content: center; width: 2.25rem; }
.dashboard-stat-trend { color: var(--dashboard-muted); font-size: .72rem; font-weight: 700; }.dashboard-stat-label { color: var(--dashboard-muted); font-size: .74rem; font-weight: 800; letter-spacing: .06em; margin-top: 1.15rem; text-transform: uppercase; }.dashboard-stat-value { font-size: 2rem; font-weight: 800; line-height: 1; margin-top: .35rem; }
.dashboard-grid { display: grid; gap: 1rem; grid-template-columns: minmax(0, 1.65fr) minmax(260px, .75fr); }.dashboard-panel { background: #fff; border: 1px solid var(--dashboard-border); min-width: 0; }.dashboard-panel-heading { align-items: flex-start; display: flex; justify-content: space-between; padding: 1.15rem 1.25rem .8rem; }.dashboard-panel-heading h2 { font-size: 1.05rem; font-weight: 800; margin: .25rem 0 0; }
.dashboard-live { align-items: center; color: var(--dashboard-green); display: inline-flex; font-size: .72rem; font-weight: 800; gap: .35rem; }.dashboard-live span { background: var(--dashboard-green); border-radius: 50%; height: .45rem; width: .45rem; }
.live-activity { max-height: 390px; overflow-y: auto; padding: 0 1.25rem 1rem; }.activity-row { align-items: center; border-top: 1px solid #f0f2f4; display: flex; gap: .75rem; padding: .8rem 0; }.activity-avatar { align-items: center; background: #eef3f7; color: var(--dashboard-ink); display: flex; flex: 0 0 2.15rem; font-size: .75rem; font-weight: 800; height: 2.15rem; justify-content: center; }.activity-details { display: grid; min-width: 0; }.activity-details strong { font-size: .86rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }.activity-details span { color: var(--dashboard-muted); font-size: .75rem; margin-top: .15rem; }.activity-meta { align-items: flex-end; display: grid; gap: .25rem; margin-left: auto; text-align: right; }.activity-meta small { color: var(--dashboard-muted); font-size: .7rem; }.activity-badge { font-size: .65rem; font-weight: 900; padding: .25rem .4rem; }.activity-badge--in { background: #e5f5ed; color: var(--dashboard-green); }.activity-badge--out { background: #edf0f2; color: #66717a; }.activity-empty { color: var(--dashboard-muted); padding: 2rem 0; text-align: center; }
.dashboard-panel--snapshot { padding-bottom: 1.25rem; }.snapshot-value { font-size: 4rem; font-weight: 800; line-height: 1; padding: .9rem 1.25rem 0; }.dashboard-panel--snapshot > p { color: var(--dashboard-muted); font-size: .82rem; margin: .45rem 1.25rem 1.25rem; }.snapshot-bar { background: #eef1f3; height: .45rem; margin: 0 1.25rem 1.25rem; }.snapshot-bar span { background: var(--dashboard-yellow); display: block; height: 100%; }.snapshot-footer { border-top: 1px solid #f0f2f4; display: flex; justify-content: space-between; margin: 0 1.25rem; padding: .75rem 0; }.snapshot-footer span { color: var(--dashboard-muted); font-size: .78rem; }.snapshot-footer strong { font-size: .85rem; }
@media (max-width: 991.98px) { .dashboard-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }.dashboard-grid { grid-template-columns: 1fr; } }
@media (max-width: 575.98px) { .dashboard-heading { align-items: flex-start; flex-direction: column; }.dashboard-actions { justify-content: flex-start; }.dashboard-stats { grid-template-columns: 1fr; } }
CSS); $this->registerJs("window.setInterval(function () { window.location.reload(); }, 10000);"); ?>

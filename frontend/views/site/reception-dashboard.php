<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var string $branchName */
/** @var common\models\Visit[] $visits */
/** @var bool $shiftStarted */
/** @var array<string, string|int> $shift */

use common\models\Visit;
use yii\helpers\Html;

$this->title = 'Reception Dashboard';
$this->params['breadcrumbs'][] = $this->title;
$shiftStarted = $shiftStarted ?? false;
$shift = is_array($shift ?? null) ? $shift : [];
if (!$shiftStarted):
?>
<div class="reception-dashboard">
    <div class="card border-0 shadow-sm mx-auto" style="max-width: 720px;">
        <div class="card-body p-4 p-lg-5 text-center">
            <span class="text-uppercase small fw-semibold text-primary">Reception workspace</span>
            <h1 class="h2 mt-2">Start Your Shift</h1>
            <span class="badge text-bg-secondary mb-3">Shift Not Started</span>
            <p class="text-body-secondary">You are signed in for <?= Html::encode($branchName) ?>. Start the shift to open today's reception dashboard.</p>
            <?php if (isset($shift['stopped_at'])): ?>
                <p class="small text-body-secondary mb-3">Last shift: <?= Html::encode((string) ($shift['started_at'] ?? '—')) ?> - <?= Html::encode((string) $shift['stopped_at']) ?></p>
            <?php endif; ?>
            <?= Html::beginForm(['/site/start-shift'], 'post') ?>
                <?= Html::submitButton('Start Shift', ['class' => 'btn btn-success btn-lg']) ?>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>
<?php return; endif;
$totalVisitors = count($visits);
$currentInside = count(array_filter($visits, static fn (Visit $visit): bool => $visit->isCheckedIn()));
$totalCheckedOut = count(array_filter($visits, static fn (Visit $visit): bool => !$visit->isCheckedIn()));
?>
<div class="reception-dashboard">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <span class="text-uppercase small fw-semibold text-primary">Reception workspace</span>
            <h1 class="h2 mt-2 mb-1">Reception Dashboard</h1>
            <p class="text-body-secondary mb-0"><?= Html::encode($branchName) ?></p>
            <span class="badge text-bg-success mt-2">Shift Started</span>
            <p class="small text-body-secondary mb-0 mt-2">Started at: <?= Html::encode((string) ($shift['started_at'] ?? '—')) ?> | Stopped at: Not yet</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <?= Html::a('Check-In Visitor', ['/visitor/check-in'], ['class' => 'btn btn-success']) ?>
            <?= Html::a('Check-Out Visitor', ['/visitor/checkout-page'], ['class' => 'btn btn-primary']) ?>
            <?= Html::beginForm(['/site/close-shift'], 'post', ['class' => 'd-inline']) ?>
                <?= Html::submitButton('Close Shift', ['class' => 'btn btn-outline-danger', 'data-confirm' => 'Close the current reception shift?']) ?>
            <?= Html::endForm() ?>
            <?= Html::beginForm(['/site/change-branch'], 'post', ['class' => 'd-inline']) ?>
                <?= Html::submitButton('Change Branch', ['class' => 'btn btn-outline-secondary']) ?>
            <?= Html::endForm() ?>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card border-0 shadow-sm h-100"><div class="card-body"><span class="text-body-secondary small text-uppercase fw-semibold">Total visitors</span><div class="display-6 fw-bold mt-2"><?= $totalVisitors ?></div></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm h-100 border-start border-4 border-success"><div class="card-body"><span class="text-body-secondary small text-uppercase fw-semibold">Current inside</span><div class="display-6 fw-bold mt-2 text-success"><?= $currentInside ?></div></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm h-100 border-start border-4 border-secondary"><div class="card-body"><span class="text-body-secondary small text-uppercase fw-semibold">Total checked out</span><div class="display-6 fw-bold mt-2 text-secondary"><?= $totalCheckedOut ?></div></div></div></div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="d-flex justify-content-between align-items-center gap-2 p-3 border-bottom">
                <div><h2 class="h5 mb-1">Branch visitors for today</h2><p class="text-body-secondary small mb-0">Visitors currently inside and visitors who have checked out.</p></div>
                <span class="badge text-bg-light border"><?= $totalVisitors ?> records</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Visitor</th><th>Phone</th><th>Host</th><th>Check-in</th><th>Check-out</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php if ($visits === []): ?>
                        <tr><td colspan="6" class="text-center text-body-secondary py-5">No visitors recorded for this branch yet.</td></tr>
                    <?php else: foreach ($visits as $visit): ?>
                        <?php $inside = $visit->isCheckedIn(); ?>
                        <tr>
                            <td class="fw-semibold"><?= Html::encode($visit->visitor?->full_name ?? 'Unknown visitor') ?></td>
                            <td><?= Html::encode($visit->visitor?->phone_number ?? '—') ?></td>
                            <td><?= Html::encode($visit->host?->username ?? 'Unassigned') ?></td>
                            <td><?= Html::encode($visit->check_in_time ?: '—') ?></td>
                            <td><?= Html::encode($visit->check_out_time ?: '—') ?></td>
                            <td><span class="badge text-bg-<?= $inside ? 'success' : 'secondary' ?>"><?= $inside ? 'Inside' : 'Checked out' ?></span></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

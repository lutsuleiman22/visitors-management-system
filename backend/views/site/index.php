<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var int $totalVisitsToday */
/** @var int $currentlyInside */
/** @var int $totalCheckedOut */
/** @var common\models\Visit[] $recentVisits */

use common\models\Visit;
use yii\helpers\Html;

$this->title = 'Dashboard';
$username = Yii::$app->user->identity?->username;
?>
<div class="site-index">
    <div class="dashboard-banner text-white rounded-4 p-4 p-lg-5 mb-4">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="fw-bold mb-2">Welcome back, <?= Html::encode($username) ?></h1>
                <p class="opacity-75 mb-0">
                    Visitor Management dashboard — monitor today’s traffic and who is still on site.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0 d-flex flex-wrap gap-2 justify-content-lg-end">
                <?= Html::a('Manage Visits', ['/visit/index'], ['class' => 'btn btn-light']) ?>
                <?= Html::a('Evacuation List', ['/visit/evacuation'], ['class' => 'btn btn-warning']) ?>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-body-secondary small text-uppercase fw-semibold">Total Visits Today</div>
                    <div class="display-5 fw-bold text-primary"><?= (int) $totalVisitsToday ?></div>
                    <div class="small text-body-secondary">Checked in since midnight</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-success">
                <div class="card-body">
                    <div class="text-body-secondary small text-uppercase fw-semibold">Currently Inside</div>
                    <div class="display-5 fw-bold text-success"><?= (int) $currentlyInside ?></div>
                    <div class="small text-body-secondary">check_out_time is empty</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-body-secondary small text-uppercase fw-semibold">Total Checked-Out</div>
                    <div class="display-5 fw-bold"><?= (int) $totalCheckedOut ?></div>
                    <div class="small text-body-secondary">Completed departures</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <h2 class="h5 mb-0">Recent Visits</h2>
            <?= Html::a('View all', ['/visit/index'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Visitor</th>
                    <th>Host</th>
                    <th>Purpose</th>
                    <th>Status</th>
                    <th>Check-In</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($recentVisits === []): ?>
                    <tr>
                        <td colspan="6" class="text-center text-body-secondary py-4">No visits recorded yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentVisits as $visit): ?>
                        <tr>
                            <td><?= Html::a((string) $visit->id, ['/visit/view', 'id' => $visit->id]) ?></td>
                            <td><?= Html::encode($visit->visitor->full_name ?? '—') ?></td>
                            <td><?= Html::encode($visit->host->username ?? '—') ?></td>
                            <td><?= Html::encode($visit->purpose) ?></td>
                            <td>
                                <?php $class = $visit->status === Visit::STATUS_CHECKED_IN ? 'success' : 'dark'; ?>
                                <span class="badge text-bg-<?= $class ?>"><?= Html::encode($visit->status) ?></span>
                            </td>
                            <td><?= Html::encode($visit->check_in_time ?: '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

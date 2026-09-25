<?php

declare(strict_types=1);

use common\models\ReceptionShift;
use yii\helpers\Html;

$this->title = 'Shift Registration';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <div class="text-uppercase small fw-semibold text-primary">Operations control</div>
        <h1 class="h2 mb-1">Shift Registration</h1>
        <p class="text-body-secondary mb-0">View reception shift registration history by branch.</p>
    </div>
    <?= Html::a('Back to Dashboard', ['/admin/dashboard'], ['class' => 'btn btn-warning']) ?>
</div>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Reception</th><th>Branch</th><th>Timetable</th><th>Started</th><th>Stopped</th><th>Status</th></tr></thead>
            <tbody>
            <?php if ($shifts === []): ?>
                <tr><td colspan="5" class="text-center text-body-secondary py-5">No shifts registered yet.</td></tr>
            <?php else: foreach ($shifts as $shift): ?>
                <tr>
                    <td class="fw-semibold"><?= Html::encode($shift->user?->username ?? 'Unknown user') ?></td>
                    <td><?= Html::encode($shift->branch_code) ?></td>
                    <td><?= Html::encode((string) ($shift->timetable ?? 'Not set')) ?></td>
                    <td><?= Html::encode((string) $shift->started_at) ?></td>
                    <td><?= Html::encode((string) ($shift->stopped_at ?? 'Not stopped')) ?></td>
                    <td><span class="badge text-bg-<?= $shift->status === ReceptionShift::STATUS_OPEN ? 'success' : 'secondary' ?>"><?= $shift->status === ReceptionShift::STATUS_OPEN ? 'Open' : 'Closed' ?></span></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

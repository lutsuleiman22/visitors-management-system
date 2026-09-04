<?php

declare(strict_types=1);

use yii\helpers\Html;

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
    <?php foreach ([['Users', $totalUsers, 'primary'], ['Visitors', $totalVisitors, 'info'], ['Visits', $totalVisits, 'dark'], ['Currently Inside', $activeVisits, 'success']] as [$label, $value, $color]): ?>
        <div class="col-sm-6 col-xl-3"><div class="card h-100 border-0 shadow-sm border-start border-4 border-<?= $color ?>"><div class="card-body"><div class="text-body-secondary small text-uppercase fw-semibold"><?= Html::encode($label) ?></div><div class="display-6 fw-bold mt-2"><?= (int) $value ?></div></div></div></div>
    <?php endforeach; ?>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h2 class="h5 mb-3">System Overview</h2>
        <div class="row g-3">
            <div class="col-md-4"><div class="bg-body-tertiary rounded-3 p-3"><div class="small text-body-secondary">Active visitors</div><strong><?= (int) $activeVisits ?> currently on site</strong></div></div>
            <div class="col-md-4"><div class="bg-body-tertiary rounded-3 p-3"><div class="small text-body-secondary">Visit activity</div><strong><?= (int) $totalVisits ?> total recorded visits</strong></div></div>
            <div class="col-md-4"><div class="bg-body-tertiary rounded-3 p-3"><div class="small text-body-secondary">User accounts</div><strong><?= (int) $totalUsers ?> registered accounts</strong></div></div>
        </div>
    </div>
</div>

<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var array<string, string> $branches */
/** @var array<string, array<string, string>> $departments */

use yii\helpers\Html;

$this->title = 'Branches & Departments';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="branch-directory">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <span class="text-uppercase small fw-semibold text-primary">PBZ network setup</span>
            <h1 class="h2 mt-2 mb-1">Branches & Departments</h1>
            <p class="text-body-secondary mb-0">All PBZ branches and the departments available for visitor registration.</p>
        </div>
        <?= Html::a('Back to Dashboard', ['/admin/dashboard'], ['class' => 'btn btn-outline-primary']) ?>
    </div>

    <div class="row g-4">
        <?php foreach ($branches as $code => $branchName): ?>
            <div class="col-md-6 col-xl-4">
                <section class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                            <div><span class="text-uppercase small fw-semibold text-primary">PBZ branch</span><h2 class="h5 mt-1 mb-0"><?= Html::encode($branchName) ?></h2></div>
                            <span class="badge text-bg-light border"><?= count($departments[$code] ?? []) ?></span>
                        </div>
                        <?php if (($departments[$code] ?? []) === []): ?>
                            <p class="text-body-secondary small mb-0">No departments configured.</p>
                        <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($departments[$code] as $departmentCode => $departmentName): ?>
                                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center"><span><?= Html::encode($departmentName) ?></span><small class="text-body-secondary"><?= Html::encode($departmentCode) ?></small></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        <?php endforeach; ?>
    </div>
</div>

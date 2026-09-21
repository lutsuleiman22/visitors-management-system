<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var array<string, string> $branches */
/** @var array<string, array<string, string>> $departments */
/** @var common\models\Branch[] $branchModels */

use yii\helpers\Html;

$this->title = 'Branches & Departments';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="branch-directory">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <span class="text-uppercase small fw-semibold text-primary">PBZ network setup</span>
            <h1 class="h2 mt-2 mb-1">Branch dashboard</h1>
            <p class="text-body-secondary mb-0">All PBZ branches and the departments available .</p>
        </div>
        <?= Html::a('Back to Dashboard', ['/admin/dashboard'], ['class' => 'btn btn-outline-primary']) ?>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12"><div class="card border-0 shadow-sm"><div class="card-body p-4"><h2 class="h5">Upload branches file</h2><?= Html::beginForm(['/admin/upload-branches'], 'post', ['enctype' => 'multipart/form-data', 'class' => 'row g-2 align-items-end mt-2']) ?><div class="col-md-8"><label class="form-label" for="branches-file">Upload file</label><input id="branches-file" type="file" name="branches_file" class="form-control" accept=".csv,.xlsx,.xls" required></div><div class="col-md-4 d-grid"><button type="submit" class="btn btn-primary">Upload file</button></div><?= Html::endForm() ?></div></div></div>
        <div class="col-lg-5"><div class="card border-0 shadow-sm h-100"><div class="card-body p-4"><h2 class="h5">Add branch</h2><p class="small text-body-secondary">Create a new PBZ branch .</p><?= Html::beginForm(['/admin/add-branch'], 'post') ?><div class="mb-3"><label class="form-label" for="branch-name">Branch name</label><input id="branch-name" name="Branch[name]" class="form-control" placeholder="PBZ Stone Town Branch" required></div><?= Html::submitButton('Add Branch', ['class' => 'btn btn-primary']) ?><?= Html::endForm() ?></div></div></div>
        <div class="col-lg-7"><div class="card border-0 shadow-sm h-100"><div class="card-body p-4"><h2 class="h5">Add department</h2><p class="small text-body-secondary">Add a department under any existing PBZ branch.</p><?= Html::beginForm(['/admin/add-department'], 'post') ?><div class="row g-3"><div class="col-md-6"><label class="form-label" for="department-branch">Branch</label><?= Html::dropDownList('Department[branch_id]', '', \yii\helpers\ArrayHelper::map(\common\models\Branch::find()->where(['status' => 1])->orderBy(['name' => SORT_ASC])->all(), 'id', 'name'), ['id' => 'department-branch', 'class' => 'form-select', 'prompt' => 'Select branch', 'required' => true]) ?></div><div class="col-12"><label class="form-label" for="department-name">Department name</label><input id="department-name" name="Department[name]" class="form-control" placeholder="Customer Care" required></div></div><?= Html::submitButton('Add Department', ['class' => 'btn btn-primary mt-3']) ?><?= Html::endForm() ?></div></div></div>
    </div>

    <div class="row g-4">
        <?php foreach ($branchModels as $branch): ?>
            <?php $code = $branch->code; $branchName = $branch->name; ?>
            <div class="col-md-6 col-xl-4">
                <section class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                            <div><span class="text-uppercase small fw-semibold text-primary">PBZ branch</span><h2 class="h5 mt-1 mb-0"><?= Html::encode($branchName) ?></h2></div>
                            <span class="badge text-bg-light border"><?= count($departments[$code] ?? []) ?></span>
                        </div>
                        <div class="d-flex gap-2 mb-3"><?= Html::a('View', ['/admin/view-branch', 'id' => $branch->id], ['class' => 'btn btn-sm btn-outline-primary']) ?><?= Html::a('Edit', ['/admin/update-branch', 'id' => $branch->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?><?= Html::beginForm(['/admin/delete-branch', 'id' => $branch->id], 'post', ['class' => 'd-inline']) ?><?= Html::submitButton('Delete', ['class' => 'btn btn-sm btn-outline-danger', 'data-confirm' => 'Delete this branch? It must have no users or visits.']) ?><?= Html::endForm() ?></div>
                        <?php if (($departments[$code] ?? []) === []): ?>
                            <p class="text-body-secondary small mb-0">No departments configured.</p>
                        <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($branch->departments as $department): ?>
                                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center"><span><?= Html::encode($department->name) ?></span><span class="d-flex gap-1"><?= Html::a('Edit', ['/admin/update-department', 'id' => $department->id], ['class' => 'btn btn-sm btn-link']) ?><?= Html::beginForm(['/admin/delete-department', 'id' => $department->id], 'post', ['class' => 'd-inline']) ?><?= Html::submitButton('Delete', ['class' => 'btn btn-sm btn-link text-danger', 'data-confirm' => 'Delete this department?']) ?><?= Html::endForm() ?></span></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        <?php endforeach; ?>
    </div>
</div>

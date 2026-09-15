<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var common\models\Branch $model */

use yii\helpers\Html;

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Branches & Departments', 'url' => ['branches']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4"><div><span class="text-uppercase small fw-semibold text-primary">PBZ branch</span><h1 class="h2 mt-2 mb-1"><?= Html::encode($model->name) ?></h1><p class="text-body-secondary mb-0"><?= count($model->departments) ?> departments configured</p></div><div class="d-flex gap-2"><?= Html::a('Edit Branch', ['update-branch', 'id' => $model->id], ['class' => 'btn btn-primary']) ?><?= Html::a('Back', ['branches'], ['class' => 'btn btn-outline-secondary']) ?></div></div>
<div class="card border-0 shadow-sm"><div class="card-body p-4"><h2 class="h5 mb-3">Departments</h2><?php if ($model->departments === []): ?><p class="text-body-secondary">No departments configured.</p><?php else: ?><div class="list-group list-group-flush"><?php foreach ($model->departments as $department): ?><div class="list-group-item px-0 d-flex justify-content-between align-items-center"><strong><?= Html::encode($department->name) ?></strong><span class="d-flex gap-2"><?= Html::a('Edit', ['update-department', 'id' => $department->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?><?= Html::beginForm(['delete-department', 'id' => $department->id], 'post', ['class' => 'd-inline']) ?><?= Html::submitButton('Delete', ['class' => 'btn btn-sm btn-outline-danger', 'data-confirm' => 'Delete this department?']) ?><?= Html::endForm() ?></span></div><?php endforeach; ?></div><?php endif; ?></div></div>

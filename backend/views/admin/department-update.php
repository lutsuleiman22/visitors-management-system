<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var common\models\Department $model */

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$this->title = 'Edit Department';
$this->params['breadcrumbs'][] = ['label' => 'Branches & Departments', 'url' => ['branches']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="card border-0 shadow-sm"><div class="card-body p-4" style="max-width: 640px"><h1 class="h3 mb-3">Edit Department</h1><p class="text-body-secondary">Branch: <?= Html::encode($model->branch->name ?? 'Unknown branch') ?></p><?php $form = ActiveForm::begin(); ?><?= $form->field($model, 'name')->textInput(['maxlength' => true, 'autofocus' => true]) ?><div class="d-flex gap-2"><?= Html::submitButton('Update Department', ['class' => 'btn btn-primary']) ?><?= Html::a('Cancel', ['view-branch', 'id' => $model->branch_id], ['class' => 'btn btn-outline-secondary']) ?></div><?php ActiveForm::end(); ?></div></div>

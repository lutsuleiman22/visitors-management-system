<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var common\models\Branch $model */

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$this->title = 'Edit Branch';
$this->params['breadcrumbs'][] = ['label' => 'Branches & Departments', 'url' => ['branches']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="card border-0 shadow-sm"><div class="card-body p-4" style="max-width: 640px"><h1 class="h3 mb-3">Edit Branch</h1><?php $form = ActiveForm::begin(); ?><?= $form->field($model, 'name')->textInput(['maxlength' => true, 'autofocus' => true]) ?><div class="d-flex gap-2"><?= Html::submitButton('Update Branch', ['class' => 'btn btn-primary']) ?><?= Html::a('Cancel', ['branches'], ['class' => 'btn btn-outline-secondary']) ?></div><?php ActiveForm::end(); ?></div></div>

<?php

declare(strict_types=1);

use common\models\User;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$this->title = 'Visitor Reports';
$this->params['breadcrumbs'][] = $this->title;
?>
<h1 class="h3 mb-3">Visitor Reports</h1>
<?php $form = ActiveForm::begin(['method' => 'get']); ?>
<div class="row g-3">
    <div class="col-md-3"><?= $form->field($model, 'from_date')->input('date') ?></div>
    <div class="col-md-3"><?= $form->field($model, 'to_date')->input('date') ?></div>
    <div class="col-md-3"><?= $form->field($model, 'role')->dropDownList(User::roleList(), ['prompt' => 'All roles']) ?></div>
    <div class="col-md-3"><?= $form->field($model, 'status')->dropDownList(['inside' => 'Inside', 'out' => 'Checked out', 'pending' => 'Pending'], ['prompt' => 'All statuses']) ?></div>
</div>
<?= Html::submitButton('Apply Filters', ['class' => 'btn btn-primary']) ?>
<?= Html::a('Export PDF', array_merge(['pdf'], Yii::$app->request->queryParams), ['class' => 'btn btn-outline-danger']) ?>
<?= Html::a('Export Excel', array_merge(['excel'], Yii::$app->request->queryParams), ['class' => 'btn btn-outline-success']) ?>
<?php ActiveForm::end(); ?>

<?php

declare(strict_types=1);

use common\models\User;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

$this->title = 'Visitor Reports';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="mb-3 d-flex flex-wrap gap-2">
    <?= Html::a('← Back', ['/admin/reports'], ['class' => 'btn btn-light border']) ?>
    <?= Html::a('🏠 Dashboard', ['/admin/dashboard'], ['class' => 'btn btn-warning']) ?>
</div>
<h1 class="h3 mb-3">Visitor Reports</h1>
<?php $form = ActiveForm::begin(['method' => 'get']); ?>
<div class="row g-3">
    <div class="col-md-3"><?= $form->field($model, 'from_date')->input('date') ?></div>
    <div class="col-md-3"><?= $form->field($model, 'to_date')->input('date') ?></div>
    <div class="col-md-3"><?= $form->field($model, 'branch')->dropDownList($branches, ['prompt' => 'All branches']) ?></div>
    <div class="col-md-3"><?= $form->field($model, 'reception_id')->dropDownList(ArrayHelper::map($receptions, 'id', 'username'), ['prompt' => 'All reception']) ?></div>
    <div class="col-md-3"><?= $form->field($model, 'role')->dropDownList(User::roleList(), ['prompt' => 'All roles']) ?></div>
    <div class="col-md-3"><?= $form->field($model, 'status')->dropDownList(['inside' => 'Inside', 'out' => 'Checked out', 'pending' => 'Pending'], ['prompt' => 'All statuses']) ?></div>
</div>
<?= Html::submitButton('Apply Filters', ['class' => 'btn btn-primary']) ?>
<?= Html::a('Export PDF', array_merge(['pdf'], Yii::$app->request->queryParams), ['class' => 'btn btn-outline-danger']) ?>
<?= Html::a('Export Excel', array_merge(['excel'], Yii::$app->request->queryParams), ['class' => 'btn btn-outline-success']) ?>
<?php ActiveForm::end(); ?>
<?php $this->registerJs('const receptionOptionsByBranch = ' . Json::htmlEncode($receptionsByBranch) . '; const branchSelect = document.getElementById("reportfilter-branch"); const receptionSelect = document.getElementById("reportfilter-reception_id"); if (branchSelect && receptionSelect) { const selectedReception = ' . Json::htmlEncode((string) $model->reception_id) . '; const refreshReceptionOptions = function () { const options = receptionOptionsByBranch[branchSelect.value] || []; receptionSelect.innerHTML = ""; const allOption = new Option("All reception", ""); receptionSelect.add(allOption); options.forEach(function (reception) { const option = new Option(reception.username, reception.id); option.selected = String(reception.id) === selectedReception; receptionSelect.add(option); }); }; branchSelect.addEventListener("change", refreshReceptionOptions); }'); ?>

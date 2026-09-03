<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var common\models\Visit $model */
/** @var array<int, string> $visitors */
/** @var array<int, string> $hosts */

use common\models\Visit;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

?>
<div class="visit-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row g-3">
        <div class="col-md-6">
            <?= $form->field($model, 'visitor_id')->dropDownList($visitors, ['prompt' => 'Select visitor…']) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'host_user_id')->dropDownList($hosts, ['prompt' => 'Select host…']) ?>
        </div>
        <div class="col-md-8">
            <?= $form->field($model, 'purpose')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'status')->dropDownList(Visit::statusList()) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'check_in_time')->textInput(['placeholder' => 'YYYY-MM-DD HH:MM:SS']) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'check_out_time')->textInput(['placeholder' => 'YYYY-MM-DD HH:MM:SS']) ?>
        </div>
        <div class="col-md-12">
            <?= $form->field($model, 'qr_code_hash')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="form-group mt-3">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', [
            'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary',
        ]) ?>
        <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

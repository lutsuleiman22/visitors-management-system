<?php

/** @var yii\bootstrap5\ActiveForm $form */
/** @var frontend\models\CheckInForm $model */
/** @var array<int, string> $hosts */
use yii\helpers\Html;
?>
<div class="row g-4">
    <div class="col-md-6">
        <?= $form->field($model, 'full_name')->textInput(['maxlength' => true, 'placeholder' => 'e.g. Jane Doe', 'autofocus' => true]) ?>
        <?= $form->field($model, 'phone_number')->textInput(['maxlength' => true, 'placeholder' => 'e.g. +255 700 000 000']) ?>
        <?= $form->field($model, 'national_id')->textInput(['maxlength' => true, 'placeholder' => 'ID number / passport (optional)']) ?>
        <?= $form->field($model, 'origin')->textInput(['maxlength' => true, 'placeholder' => 'Alipotokea / Anapokaa']) ?>
        <?= $form->field($model, 'host_user_id')->dropDownList($hosts, ['prompt' => 'Select host']) ?>
        <?= $form->field($model, 'purpose')->textarea(['rows' => 3, 'placeholder' => 'Brief reason for your visit']) ?>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Signature <span class="text-danger">*</span></label>
        <div class="border rounded-3 p-3 bg-body-tertiary">
            <canvas id="signature-canvas" class="w-100 border rounded bg-white" height="220" aria-label="Signature capture area"></canvas>
            <div class="d-flex justify-content-between align-items-center mt-2">
                <span id="signature-status" class="small text-body-secondary">Sign inside the box above.</span>
                <?= Html::button('Clear', ['type' => 'button', 'id' => 'clear-signature', 'class' => 'btn btn-outline-secondary btn-sm']) ?>
            </div>
        </div>
        <?= $form->field($model, 'signature_data')->hiddenInput()->label(false) ?>
    </div>
</div>

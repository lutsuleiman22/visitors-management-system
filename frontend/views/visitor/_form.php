<?php

/** @var yii\bootstrap5\ActiveForm $form */
/** @var frontend\models\CheckInForm $model */
/** @var array<int, string> $hosts */
/** @var array<string, string> $departments */
use yii\helpers\Html;
?>
<div class="row g-3">
    <div class="col-md-6">
        <?= $form->field($model, 'full_name')->textInput(['maxlength' => true, 'placeholder' => 'e.g. Jane Doe', 'autofocus' => true]) ?>
        <?= $form->field($model, 'phone_number')->textInput(['maxlength' => true, 'placeholder' => 'e.g. +255 700 000 000']) ?>
        <?= $form->field($model, 'gender')->dropDownList(['Male' => 'Male', 'Female' => 'Female', 'Other' => 'Other'], ['prompt' => 'Select gender']) ?>
        <?= $form->field($model, 'origin')->textInput(['maxlength' => true, 'placeholder' => 'From']) ?>
        <?= $form->field($model, 'destination')->textInput(['maxlength' => true, 'placeholder' => 'To']) ?>
        <?= $form->field($model, 'department_code')->dropDownList($departments, ['prompt' => 'Select department (optional)']) ?>
        <?= $form->field($model, 'host_name')->textInput(['maxlength' => true, 'list' => 'host-list', 'placeholder' => 'Search or type host name (optional)']) ?>
        <datalist id="host-list">
            <?php foreach ($hosts as $id => $name): ?>
                <option value="<?= Html::encode($name) ?>"><?= Html::encode($name) ?></option>
            <?php endforeach; ?>
        </datalist>
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold">Signature <span class="text-danger">*</span></label>
        <div class="signature-box">
            <canvas id="signature-canvas" class="signature-pad border rounded bg-white" width="350" height="130" aria-label="Signature capture area"></canvas>
            <div class="d-flex justify-content-between align-items-center mt-2">
                <span id="signature-status" class="small text-body-secondary">Sign inside the box above.</span>
                <?= Html::button('Clear', ['type' => 'button', 'id' => 'clear-signature', 'class' => 'btn btn-outline-secondary btn-sm']) ?>
            </div>
        </div>
        <?= $form->field($model, 'signature_data')->hiddenInput(['id' => 'signature-data'])->label(false) ?>
    </div>
</div>

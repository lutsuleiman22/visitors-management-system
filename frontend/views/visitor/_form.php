<?php

/** @var yii\bootstrap5\ActiveForm $form */
/** @var frontend\models\CheckInForm $model */
/** @var array<int, string> $hosts */
use yii\helpers\Html;
?>
<div class="row g-3">
    <div class="col-md-6">
        <?= $form->field($model, 'full_name')->textInput(['maxlength' => true, 'placeholder' => 'e.g. Jane Doe', 'autofocus' => true]) ?>
        <?= $form->field($model, 'phone_number')->textInput(['maxlength' => true, 'placeholder' => 'e.g. +255 700 000 000']) ?>
        <?= $form->field($model, 'gender')->dropDownList(['Male' => 'Male', 'Female' => 'Female', 'Other' => 'Other'], ['prompt' => 'Select gender']) ?>
        <?= $form->field($model, 'origin')->textInput(['maxlength' => true, 'placeholder' => 'Where are you coming from?']) ?>
        <?= $form->field($model, 'destination')->textInput(['maxlength' => true, 'placeholder' => 'Where are you going?']) ?>
        <?= $form->field($model, 'host_name')->textInput(['maxlength' => true, 'list' => 'host-list', 'placeholder' => 'Search or type host name']) ?>
        <datalist id="host-list">
            <?php foreach ($hosts as $id => $name): ?>
                <option value="<?= Html::encode($name) ?>"><?= Html::encode($name) ?></option>
            <?php endforeach; ?>
        </datalist>
    </div>
    <div class="col-md-6">
        <div class="h-100 d-flex flex-column justify-content-between gap-3">
            <div class="bg-light border rounded p-3 h-100">
                <div class="small text-uppercase text-muted fw-bold mb-2">Visitor summary</div>
                <p class="mb-2">Complete the visitor details, preview them, and confirm the registration before final submission.</p>
                <ul class="small text-muted mb-0 ps-3">
                    <li>Step 1: enter visitor details</li>
                    <li>Step 2: check the summary</li>
                    <li>Step 3: confirm and save</li>
                </ul>
            </div>
        </div>
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

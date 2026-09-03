<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var frontend\models\CheckInForm $model */
/** @var array<int, string> $hosts */

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$this->title = 'Visitor Check-In';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="visitor-check-in">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-primary text-white py-3">
                    <h1 class="h4 mb-0"><?= Html::encode($this->title) ?></h1>
                    <p class="mb-0 small opacity-75">Complete the form, select your host, and capture a photo.</p>
                </div>
                <div class="card-body p-4">
                    <?php $form = ActiveForm::begin([
                        'id' => 'check-in-form',
                        'options' => ['autocomplete' => 'off'],
                    ]); ?>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <?= $form->field($model, 'full_name')->textInput([
                                'maxlength' => true,
                                'placeholder' => 'e.g. Jane Doe',
                                'autofocus' => true,
                            ]) ?>

                            <?= $form->field($model, 'phone_number')->textInput([
                                'maxlength' => true,
                                'placeholder' => 'e.g. +1 555 0100',
                            ]) ?>

                            <?= $form->field($model, 'national_id')->textInput([
                                'maxlength' => true,
                                'placeholder' => 'National ID / Passport',
                            ]) ?>

                            <?= $form->field($model, 'host_user_id')->dropDownList($hosts, [
                                'prompt' => 'Select host…',
                            ]) ?>

                            <?= $form->field($model, 'purpose')->textarea([
                                'rows' => 3,
                                'placeholder' => 'Brief reason for your visit',
                            ]) ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Webcam Photo <span class="text-danger">*</span></label>
                            <div class="webcam-panel border rounded-3 p-3 bg-body-tertiary">
                                <div class="ratio ratio-4x3 mb-3 bg-dark rounded overflow-hidden">
                                    <video id="webcam" autoplay playsinline muted class="w-100 h-100" style="object-fit: cover;"></video>
                                    <canvas id="photo-canvas" class="d-none"></canvas>
                                    <img id="photo-preview" alt="Captured photo" class="d-none w-100 h-100" style="object-fit: cover;">
                                </div>
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    <button type="button" id="btn-start-camera" class="btn btn-outline-secondary btn-sm">Start Camera</button>
                                    <button type="button" id="btn-capture" class="btn btn-primary btn-sm" disabled>Capture Photo</button>
                                    <button type="button" id="btn-retake" class="btn btn-outline-warning btn-sm d-none">Retake</button>
                                </div>
                                <div id="camera-status" class="small text-body-secondary">Click “Start Camera” and allow browser access.</div>
                            </div>
                            <?= $form->field($model, 'photo_data')->hiddenInput(['id' => 'photo-data'])->label(false) ?>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                        <?= Html::a('Check-Out Instead', ['check-out'], ['class' => 'btn btn-link']) ?>
                        <?= Html::submitButton('Complete Check-In', [
                            'class' => 'btn btn-success btn-lg px-4',
                            'id' => 'btn-submit-checkin',
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$js = <<<'JS'
(function () {
    const video = document.getElementById('webcam');
    const canvas = document.getElementById('photo-canvas');
    const preview = document.getElementById('photo-preview');
    const photoData = document.getElementById('photo-data');
    const statusEl = document.getElementById('camera-status');
    const btnStart = document.getElementById('btn-start-camera');
    const btnCapture = document.getElementById('btn-capture');
    const btnRetake = document.getElementById('btn-retake');
    let stream = null;

    function setStatus(message, isError) {
        statusEl.textContent = message;
        statusEl.className = 'small ' + (isError ? 'text-danger' : 'text-body-secondary');
    }

    async function startCamera() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
                audio: false
            });
            video.srcObject = stream;
            video.classList.remove('d-none');
            preview.classList.add('d-none');
            btnCapture.disabled = false;
            btnRetake.classList.add('d-none');
            setStatus('Camera ready. Position your face and click Capture Photo.');
        } catch (err) {
            setStatus('Unable to access camera: ' + err.message, true);
        }
    }

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(function (track) { track.stop(); });
            stream = null;
        }
    }

    function capturePhoto() {
        const width = video.videoWidth || 640;
        const height = video.videoHeight || 480;
        canvas.width = width;
        canvas.height = height;
        canvas.getContext('2d').drawImage(video, 0, 0, width, height);
        const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
        photoData.value = dataUrl;
        preview.src = dataUrl;
        preview.classList.remove('d-none');
        video.classList.add('d-none');
        btnCapture.disabled = true;
        btnRetake.classList.remove('d-none');
        stopCamera();
        setStatus('Photo captured. You can retake if needed, then submit the form.');
    }

    function retake() {
        photoData.value = '';
        preview.classList.add('d-none');
        preview.removeAttribute('src');
        video.classList.remove('d-none');
        startCamera();
    }

    btnStart.addEventListener('click', startCamera);
    btnCapture.addEventListener('click', capturePhoto);
    btnRetake.addEventListener('click', retake);

    document.getElementById('check-in-form').addEventListener('submit', function (e) {
        if (!photoData.value) {
            e.preventDefault();
            setStatus('Please capture a photo before submitting.', true);
        }
    });

    window.addEventListener('beforeunload', stopCamera);
})();
JS;
$this->registerJs($js);
?>

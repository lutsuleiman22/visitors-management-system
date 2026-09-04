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
                    <p class="mb-0 small opacity-75">Complete the visitor details and sign below.</p>
                </div>
                <div class="card-body p-4">
                    <?php $form = ActiveForm::begin([
                        'id' => 'check-in-form',
                        'options' => ['autocomplete' => 'off'],
                    ]); ?>

                    <?= $form->errorSummary($model, ['class' => 'alert alert-danger']) ?>

                    <?= $this->render('_form', ['form' => $form, 'model' => $model, 'hosts' => $hosts]) ?>

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
    const canvas = document.getElementById('signature-canvas');
    const context = canvas.getContext('2d');
    const signatureData = document.getElementById('checkinform-signature_data');
    const form = document.getElementById('check-in-form');
    let drawing = false;
    let hasSignature = false;

    function position(event) {
        const point = event.touches ? event.touches[0] : event;
        const bounds = canvas.getBoundingClientRect();
        return { x: (point.clientX - bounds.left) * canvas.width / bounds.width, y: (point.clientY - bounds.top) * canvas.height / bounds.height };
    }

    function start(event) { drawing = true; context.beginPath(); context.moveTo(position(event).x, position(event).y); event.preventDefault(); }
    function draw(event) { if (!drawing) return; const point = position(event); context.lineTo(point.x, point.y); context.stroke(); hasSignature = true; event.preventDefault(); }
    function stop() { drawing = false; }

    context.lineWidth = 2;
    context.lineCap = 'round';
    context.strokeStyle = '#17202a';
    canvas.addEventListener('pointerdown', start);
    canvas.addEventListener('pointermove', draw);
    canvas.addEventListener('pointerup', stop);
    canvas.addEventListener('pointerleave', stop);
    document.getElementById('clear-signature').addEventListener('click', function () {
        context.clearRect(0, 0, canvas.width, canvas.height);
        signatureData.value = '';
        hasSignature = false;
    });
    form.addEventListener('submit', function (event) {
        if (hasSignature) {
            signatureData.value = canvas.toDataURL('image/png');
        }
        if (!hasSignature || !signatureData.value) { event.preventDefault(); document.getElementById('signature-status').textContent = 'Please provide a signature.'; }
    });
})();
JS;
$this->registerJs($js);
?>

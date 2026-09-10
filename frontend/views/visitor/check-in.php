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
<div class="visitor-check-in-page">
    <div class="visitor-check-in-shell">
        <div class="visitor-check-in-card">
            <div class="visitor-check-in-heading">
                <span class="visitor-check-in-mark">MC</span>
                <div>
                    <span class="visitor-check-in-eyebrow">Self-service registration</span>
                    <h1><?= Html::encode($this->title) ?></h1>
                    <p>Please fill your details to register your visit.</p>
                </div>
            </div>
            <div class="visitor-check-in-body">
                    <?php $form = ActiveForm::begin([
                        'id' => 'check-in-form',
                        'options' => ['autocomplete' => 'off'],
                    ]); ?>

                    <?= $form->errorSummary($model, ['class' => 'alert alert-danger']) ?>

                    <?= $this->render('_form', ['form' => $form, 'model' => $model, 'hosts' => $hosts]) ?>

                    <div class="visitor-check-in-actions">
                        <?= Html::a('Check-Out Instead', ['check-out'], ['class' => 'btn btn-link']) ?>
                        <?= Html::submitButton('Check In', [
                            'class' => 'btn btn-checkin btn-lg px-4',
                            'id' => 'btn-submit-checkin',
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>

<?php
$js = <<<'JS'
(function () {
    const canvas = document.getElementById('signature-canvas');
    const context = canvas.getContext('2d');
    const signatureData = document.getElementById('signature-data');
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
    function syncSignature() {
        signatureData.value = canvas.toDataURL('image/png');
    }
    function stop() {
        if (drawing) syncSignature();
        drawing = false;
    }

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
        if (hasSignature) syncSignature();
        if (!hasSignature || !signatureData.value) {
            event.preventDefault();
            document.getElementById('signature-status').textContent = 'Please provide a signature.';
            return;
        }

        const submitButton = document.getElementById('btn-submit-checkin');
        submitButton.disabled = true;
        submitButton.textContent = 'Processing...';
    });
})();
JS;
$this->registerJs($js);
$this->registerCss(<<<'CSS'
.visitor-check-in-page { background: linear-gradient(135deg, #f4f7f8 0%, #eef3f1 100%); margin: -1.5rem calc(50% - 50vw) -2.5rem; min-height: calc(100vh - 9rem); padding: 3rem 1rem; }
.visitor-check-in-shell { margin: 0 auto; max-width: 900px; }
.visitor-check-in-card { background: #fff; border: 1px solid #e1e9e5; border-radius: 14px; box-shadow: 0 18px 45px rgba(23, 48, 39, .1); overflow: hidden; }
.visitor-check-in-heading { align-items: flex-start; background: #183b31; color: #fff; display: flex; gap: 1rem; padding: 2rem 2.25rem; }
.visitor-check-in-mark { align-items: center; background: #b9d8c7; color: #183b31; display: inline-flex; flex: 0 0 2.8rem; font-size: .75rem; font-weight: 900; height: 2.8rem; justify-content: center; letter-spacing: .04em; }
.visitor-check-in-eyebrow { color: #b9d8c7; display: block; font-size: .68rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
.visitor-check-in-heading h1 { font-size: 1.65rem; font-weight: 800; margin: .3rem 0 .35rem; }.visitor-check-in-heading p { color: #d8e8e0; margin: 0; }
.visitor-check-in-body { padding: 2rem 2.25rem 2.25rem; }.visitor-check-in-body .form-label { color: #33443d; font-size: .82rem; font-weight: 700; }.visitor-check-in-body .form-control, .visitor-check-in-body .form-select { border-color: #d7e1dc; border-radius: 8px; min-height: 2.8rem; padding: .7rem .8rem; }.visitor-check-in-body textarea.form-control { min-height: 6rem; }.visitor-check-in-body .form-control:focus, .visitor-check-in-body .form-select:focus { border-color: #4f9b72; box-shadow: 0 0 0 .2rem rgba(79, 155, 114, .16); }
.visitor-check-in-body .row { row-gap: .25rem; }.visitor-check-in-body .field-checkinform-signature_data { display: none; }.signature-box { background: #f8fbf9; border: 1px solid #d7e1dc; border-radius: 8px; max-width: 380px; padding: .55rem; }.signature-pad { display: block; height: 130px; max-width: 350px; touch-action: none; width: 100%; }.visitor-check-in-body canvas { border-color: #d7e1dc !important; border-radius: 6px !important; }
.visitor-check-in-actions { align-items: center; border-top: 1px solid #e8eeeb; display: flex; gap: 1rem; justify-content: space-between; margin-top: 1.25rem; padding-top: 1.25rem; }.visitor-check-in-actions .btn-link { color: #587067; text-decoration: none; }.btn-checkin { background: #8bc9a5; border: 0; border-radius: 8px; color: #123025; font-weight: 800; min-width: 190px; }.btn-checkin:hover, .btn-checkin:focus { background: #6fb88d; color: #10291f; }
@media (max-width: 575.98px) { .visitor-check-in-page { margin-left: -0.75rem; margin-right: -0.75rem; padding: 1.25rem .75rem 2rem; }.visitor-check-in-heading { padding: 1.5rem; }.visitor-check-in-body { padding: 1.5rem; }.visitor-check-in-actions { align-items: stretch; flex-direction: column-reverse; }.visitor-check-in-actions .btn-checkin { width: 100%; }.visitor-check-in-actions .btn-link { align-self: center; } }
CSS);
?>

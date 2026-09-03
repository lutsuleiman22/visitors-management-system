<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var frontend\models\CheckOutForm $model */
/** @var common\models\Visit|null $matchedVisit */

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$this->title = 'Visitor Check-Out';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="visitor-check-out">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-dark text-white py-3">
                    <h1 class="h4 mb-0"><?= Html::encode($this->title) ?></h1>
                    <p class="mb-0 small opacity-75">Scan the visitor QR code or search by name / National ID.</p>
                </div>
                <div class="card-body p-4">
                    <?php $form = ActiveForm::begin(['id' => 'check-out-form']); ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <?= $form->field($model, 'qr_code_hash')->textInput([
                                'maxlength' => true,
                                'placeholder' => 'Paste or scan QR hash',
                                'autofocus' => true,
                                'id' => 'checkoutform-qr_code_hash',
                            ])->hint('Preferred: scan the code from the visitor pass.') ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'search')->textInput([
                                'maxlength' => true,
                                'placeholder' => 'Name, National ID, or phone',
                                'id' => 'checkoutform-search',
                            ])->hint('Used when QR code is unavailable.') ?>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <?= Html::a('Back to Check-In', ['check-in'], ['class' => 'btn btn-link']) ?>
                        <?= Html::submitButton('Find Visitor', [
                            'class' => 'btn btn-primary',
                            'name' => 'find',
                            'value' => '1',
                        ]) ?>
                    </div>

                    <?php if ($matchedVisit !== null): ?>
                        <hr class="my-4">
                        <div class="alert alert-info">
                            <h2 class="h5">Active Visit Found</h2>
                            <dl class="row mb-3 small">
                                <dt class="col-sm-3">Visitor</dt>
                                <dd class="col-sm-9"><?= Html::encode($matchedVisit->visitor->full_name) ?></dd>
                                <dt class="col-sm-3">National ID</dt>
                                <dd class="col-sm-9"><?= Html::encode($matchedVisit->visitor->national_id ?: '—') ?></dd>
                                <dt class="col-sm-3">Host</dt>
                                <dd class="col-sm-9"><?= Html::encode($matchedVisit->host->username ?? '—') ?></dd>
                                <dt class="col-sm-3">Purpose</dt>
                                <dd class="col-sm-9"><?= Html::encode($matchedVisit->purpose ?: '—') ?></dd>
                                <dt class="col-sm-3">Checked In</dt>
                                <dd class="col-sm-9">
                                    <?= $matchedVisit->check_in_time
                                        ? Yii::$app->formatter->asDatetime($matchedVisit->check_in_time)
                                        : '—' ?>
                                </dd>
                                <dt class="col-sm-3">QR Hash</dt>
                                <dd class="col-sm-9"><code><?= Html::encode((string) $matchedVisit->qr_code_hash) ?></code></dd>
                            </dl>
                            <?= Html::hiddenInput('confirm_checkout', '1') ?>
                            <?= Html::submitButton('Confirm Check-Out', [
                                'class' => 'btn btn-danger',
                                'data' => [
                                    'confirm' => 'Confirm check-out for ' . $matchedVisit->visitor->full_name . '?',
                                ],
                            ]) ?>
                        </div>
                    <?php endif; ?>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

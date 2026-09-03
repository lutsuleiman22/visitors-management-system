<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var common\models\Visit $visit */

use yii\helpers\Html;

$visitor = $visit->visitor;
$host = $visit->host;
$checkIn = $visit->check_in_time
    ? Yii::$app->formatter->asDatetime($visit->check_in_time)
    : '—';
$photoUrl = $visitor->photo_path
    ? Yii::getAlias('@web/' . ltrim($visitor->photo_path, '/'))
    : null;
$qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . rawurlencode((string) $visit->qr_code_hash);

$this->title = 'Visitor Pass #' . $visit->id;
$this->params['breadcrumbs'][] = ['label' => 'Check-In', 'url' => ['check-in']];
$this->params['breadcrumbs'][] = 'Pass';
?>
<div class="visitor-pass">
    <div class="d-print-none mb-3 d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <div>
            <?= Html::a('&larr; New Check-In', ['check-in'], ['class' => 'btn btn-outline-secondary']) ?>
            <?= Html::a('Go to Check-Out', ['check-out'], ['class' => 'btn btn-outline-primary']) ?>
        </div>
        <button type="button" class="btn btn-dark" onclick="window.print()">Print Pass</button>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="pass-badge border border-2 border-dark rounded-3 overflow-hidden bg-white text-dark shadow-sm">
                <div class="bg-dark text-white text-center py-2 px-3">
                    <div class="fw-bold text-uppercase small">Visitor Pass</div>
                    <div class="small opacity-75"><?= Html::encode(Yii::$app->name) ?></div>
                </div>
                <div class="p-4">
                    <div class="row g-3 align-items-center">
                        <div class="col-4 text-center">
                            <?php if ($photoUrl): ?>
                                <?= Html::img($photoUrl, [
                                    'alt' => $visitor->full_name,
                                    'class' => 'img-fluid rounded border',
                                    'style' => 'max-height: 140px; object-fit: cover;',
                                ]) ?>
                            <?php else: ?>
                                <div class="bg-light border rounded d-flex align-items-center justify-content-center" style="height: 140px;">
                                    <span class="text-muted small">No Photo</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-8">
                            <h2 class="h4 fw-bold mb-1"><?= Html::encode($visitor->full_name) ?></h2>
                            <dl class="row mb-0 small">
                                <dt class="col-5 text-muted">National ID</dt>
                                <dd class="col-7"><?= Html::encode($visitor->national_id ?: '—') ?></dd>
                                <dt class="col-5 text-muted">Phone</dt>
                                <dd class="col-7"><?= Html::encode($visitor->phone_number) ?></dd>
                                <dt class="col-5 text-muted">Host</dt>
                                <dd class="col-7"><?= Html::encode($host->username ?? '—') ?></dd>
                                <dt class="col-5 text-muted">Purpose</dt>
                                <dd class="col-7"><?= Html::encode($visit->purpose ?: '—') ?></dd>
                                <dt class="col-5 text-muted">Check-In</dt>
                                <dd class="col-7"><?= Html::encode($checkIn) ?></dd>
                                <dt class="col-5 text-muted">Status</dt>
                                <dd class="col-7"><span class="badge text-bg-success"><?= Html::encode($visit->status) ?></span></dd>
                            </dl>
                        </div>
                    </div>
                    <hr class="my-3">
                    <div class="d-flex align-items-center justify-content-between gap-3">
                        <div>
                            <div class="small text-muted mb-1">Scan to check out</div>
                            <code class="small user-select-all"><?= Html::encode((string) $visit->qr_code_hash) ?></code>
                        </div>
                        <?= Html::img($qrUrl, [
                            'alt' => 'QR Code',
                            'width' => 120,
                            'height' => 120,
                            'class' => 'border rounded',
                        ]) ?>
                    </div>
                </div>
                <div class="bg-light border-top px-3 py-2 small text-muted text-center">
                    Pass #<?= (int) $visit->id ?> &middot; Present this badge while on premises
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$this->registerCss(<<<'CSS'
@media print {
    header, footer, .d-print-none, .breadcrumb, #theme-toggle { display: none !important; }
    main > .container { padding-top: 0 !important; }
    .pass-badge { box-shadow: none !important; }
}
CSS);
?>

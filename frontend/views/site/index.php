<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var array<string, string> $branches */
/** @var string $selectedBranch */

use yii\helpers\Html;

$this->title = 'PBZ Visitor Management System';
$this->params['meta_description'] = 'Self-service visitor check-in and check-out portal.';
?>
<div class="site-index">
    <div class="hero-banner text-white rounded-4 p-4 p-lg-5 mb-4">
        <span class="small text-uppercase fw-semibold opacity-75">PBZ front desk portal</span>
        <h1 class="display-6 fw-bold mt-2 mb-2">Visitor Operations</h1>
        <p class="lead opacity-75 mb-0">Select your branch, sign in as reception, then manage visitor check-in and check-out.</p>
    </div>

    <?php if ($selectedBranch === ''): ?>
        <div class="card border-0 shadow-sm rounded-4"><div class="card-body p-4 p-lg-5">
            <span class="text-uppercase small fw-semibold text-primary">Step 1</span>
            <h2 class="h3 mt-2">Select PBZ branch</h2>
            <p class="text-body-secondary">Choose the branch where this reception desk is operating.</p>
            <?= Html::beginForm(['/site/index'], 'post') ?>
                <div class="row g-3 align-items-end"><div class="col-md-8">
                    <label class="form-label fw-semibold" for="pbz-branch">Branch</label>
                    <?= Html::dropDownList('branch', '', $branches, ['id' => 'pbz-branch', 'class' => 'form-select form-select-lg', 'prompt' => 'Choose a PBZ branch']) ?>
                </div><div class="col-md-4 d-grid">
                    <?= Html::submitButton('Continue', ['class' => 'btn btn-primary btn-lg']) ?>
                </div></div>
            <?= Html::endForm() ?>
        </div></div>
    <?php elseif (Yii::$app->user->isGuest): ?>
        <div class="card border-0 shadow-sm rounded-4"><div class="card-body p-4 p-lg-5">
            <span class="text-uppercase small fw-semibold text-primary">Step 2</span>
            <h2 class="h3 mt-2"><?= Html::encode($branches[$selectedBranch]) ?></h2>
            <p class="text-body-secondary">Reception must sign in before visitor operations are available.</p>
            <div class="d-flex gap-2 flex-wrap">
                <?= Html::a('Reception Sign In', ['/site/login'], ['class' => 'btn btn-primary btn-lg']) ?>
                <?= Html::beginForm(['/site/change-branch'], 'post', ['class' => 'd-inline']) ?>
                    <?= Html::submitButton('Change Branch', ['class' => 'btn btn-outline-secondary btn-lg']) ?>
                <?= Html::endForm() ?>
            </div>
        </div></div>
    <?php else: ?>
        <div class="card border-0 shadow-sm rounded-4"><div class="card-body p-4 p-lg-5">
            <span class="text-uppercase small fw-semibold text-success">Ready</span>
            <h2 class="h3 mt-2"><?= Html::encode($branches[$selectedBranch]) ?></h2>
            <p class="text-body-secondary">Reception: <strong><?= Html::encode(Yii::$app->user->identity->username) ?></strong></p>
            <div class="d-flex gap-2 flex-wrap">
                <?= Html::a('Reception Dashboard', ['/site/reception-dashboard'], ['class' => 'btn btn-primary btn-lg']) ?>
                <?= Html::beginForm(['/site/change-branch'], 'post', ['class' => 'd-inline']) ?>
                    <?= Html::submitButton('Change Branch', ['class' => 'btn btn-outline-secondary btn-lg']) ?>
                <?= Html::endForm() ?>
            </div>
        </div></div>
    <?php endif; ?>
</div>
<?php $this->registerCss(<<<'CSS'
.site-index { position: relative; isolation: isolate; }
.site-index::before { background: url('/visitors-management-system/frontend/web/images/pbz%20images.png') center / min(42vw, 480px) no-repeat; content: ''; inset: 0; opacity: .08; pointer-events: none; position: absolute; z-index: 0; }
.site-index > * { position: relative; z-index: 1; }
CSS); ?>

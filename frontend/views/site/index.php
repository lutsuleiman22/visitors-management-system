<?php

declare(strict_types=1);

/** @var yii\web\View $this */
use yii\helpers\Html;

$this->title = 'PBZ Visitor Management System';
$this->params['meta_description'] = 'Self-service visitor check-in and check-out portal.';
?>
<div class="site-index">
    <div class="hero-banner text-white rounded-4 p-4 p-lg-5 mb-4">
        <span class="small text-uppercase fw-semibold opacity-75">PBZ front desk portal</span>
        <h1 class="display-6 fw-bold mt-2 mb-2">Reception Sign In</h1>
        <p class="lead opacity-75 mb-0">Sign in with your approved reception account to continue.</p>
        <?= Html::a('Reception Sign In', ['/site/login'], ['class' => 'btn btn-light btn-lg mt-4']) ?>
    </div>
</div>
<?php $this->registerCss(<<<'CSS'
.site-index { position: relative; isolation: isolate; }
.site-index::before { background: url('/visitors-management-system/frontend/web/images/pbz%20images.png') center / min(42vw, 480px) no-repeat; content: ''; inset: 0; opacity: .08; pointer-events: none; position: absolute; z-index: 0; }
.site-index > * { position: relative; z-index: 1; }
CSS); ?>

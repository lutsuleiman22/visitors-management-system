<?php

declare(strict_types=1);

/** @var yii\web\View $this */

use yii\helpers\Html;
use yii\helpers\Url;

$identity = Yii::$app->user->identity;

$items = [
    [
        'label' => 'Home',
        'url' => ['/site/index'],
    ],
];

?>
<header id="header">
    <div class="frontend-topbar-inner">
        <?= Html::a(
            Html::img(Yii::getAlias('@web/images/pbz logo.png'), [
                'alt' => 'PBZ Bank Visitor Management System',
                'class' => 'brand-logo',
                'height' => 42,
            ]) . Html::tag('span', 'Visitor-Management-System', ['class' => 'frontend-brand-name']) . Html::tag('span', 'Visitor management', ['class' => 'frontend-brand-context']),
            Yii::$app->homeUrl,
            ['class' => 'frontend-brand', 'aria-label' => 'Visitor Management System home'],
        ) ?>
        <nav class="frontend-links" aria-label="Frontend navigation">
            <?= Html::a('Home', Url::to(['/site/index']), ['class' => 'frontend-nav-link']) ?>
        </nav>
        <div class="frontend-actions">
    <?php if (!Yii::$app->user->isGuest): ?>
        <?= Html::a('Logout (' . Html::encode(Yii::$app->user->identity?->username) . ')', Url::to(['/site/logout']), ['class' => 'frontend-logout-button']) ?>
    <?php endif; ?>
    <?= Html::button(
        '&#127769;',
        [
            'id' => 'theme-toggle',
            'class' => 'frontend-theme-button',
            'aria-label' => 'Switch to dark mode',
        ],
    ) ?>
        </div>
    </div>
</header>
<?php $this->registerCss(<<<'CSS'
.frontend-topbar { background: #fff; border-bottom: 1px solid #dfe7ee; box-shadow: 0 2px 8px rgba(16,35,62,.06); min-height: 82px; }
.frontend-topbar-inner { align-items: center; display: flex; gap: 1.25rem; margin: 0 auto; max-width: 1320px; min-height: 82px; padding: 0 1rem; }
.frontend-brand { align-items: center; display: inline-flex; gap: .7rem; white-space: nowrap; }
.frontend-brand:hover { text-decoration: none; }
.frontend-brand-name { color: #10233e; font-size: 1rem; font-weight: 800; }
.frontend-brand-context { border-left: 1px solid #dfe7ee; color: #6b7c8e; font-size: .78rem; padding-left: .7rem; }
.frontend-links { display: flex; gap: .25rem; }
.frontend-nav-link { color: #53677c; font-weight: 600; padding: .55rem .7rem; text-decoration: none; }
.frontend-nav-link:hover, .frontend-nav-link:focus { color: #0d6efd; }
.frontend-actions { align-items: center; display: flex; gap: .6rem; margin-left: auto; }
.frontend-logout-button { border: 1px solid #9aa9b7; border-radius: .35rem; color: #53677c; padding: .45rem .7rem; text-decoration: none; }
.frontend-logout-button:hover, .frontend-logout-button:focus { background: #f1f5f8; color: #10233e; }
.frontend-theme-button { background: transparent; border: 0; color: #53677c; cursor: pointer; font-size: 1.25rem; padding: .35rem .5rem; }
@media (max-width: 767.98px) { .frontend-brand-name, .frontend-brand-context { display: none; } .frontend-topbar-inner { gap: .5rem; } .frontend-actions { gap: .25rem; } .frontend-logout-button { font-size: .78rem; } }
CSS); ?>

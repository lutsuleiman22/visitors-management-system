<?php

declare(strict_types=1);

/** @var yii\web\View $this */

use yii\helpers\Html;
use yii\helpers\Url;

$identity = Yii::$app->user->identity;
$backendBaseUrl = str_replace('/frontend/web', '/backend/web', rtrim(Yii::$app->request->baseUrl, '/'));

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
            ]) . Html::tag('span', 'Visitor-Management-System', ['class' => 'frontend-brand-name']) . Html::tag('span', 'Home', ['class' => 'frontend-brand-home']),
            Yii::$app->homeUrl,
            ['class' => 'frontend-brand', 'aria-label' => 'Visitor Management System home'],
        ) ?>
        <div class="frontend-actions">
            <?= Html::a('Admin Panel', $backendBaseUrl . '/index.php/site/login', ['class' => 'frontend-admin-button']) ?>
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
.frontend-topbar-inner { align-items: center; display: flex; gap: .5rem; margin: 0 auto; max-width: 1320px; min-height: 82px; padding: 0 1rem; }
.frontend-brand { align-items: center; display: inline-flex; gap: .6rem; white-space: nowrap; }
.frontend-brand:hover { text-decoration: none; }
.frontend-brand-name { color: #10233e; font-size: 1rem; font-weight: 800; }
.frontend-brand-home { color: #2d4057; font-size: .9rem; font-weight: 700; margin-left: .15rem; }
.frontend-links { display: flex; gap: .25rem; }
.frontend-nav-link { color: #53677c; font-weight: 600; padding: .55rem .7rem; text-decoration: none; }
.frontend-nav-link:hover, .frontend-nav-link:focus { color: #0d6efd; }
.frontend-actions { align-items: center; display: flex; gap: .6rem; margin-left: auto; }
.frontend-admin-button { background: #f4c542; border: 1px solid #e0b843; border-radius: .45rem; color: #10233e; font-size: .8rem; font-weight: 700; padding: .5rem .9rem; text-decoration: none; }
.frontend-admin-button:hover, .frontend-admin-button:focus { background: #e7b733; color: #10233e; text-decoration: none; }
.frontend-logout-button { border: 1px solid #9aa9b7; border-radius: .35rem; color: #53677c; padding: .45rem .7rem; text-decoration: none; }
.frontend-logout-button:hover, .frontend-logout-button:focus { background: #f1f5f8; color: #10233e; }
.frontend-theme-button { background: transparent; border: 0; color: #53677c; cursor: pointer; font-size: 1.25rem; padding: .35rem .5rem; }
@media (max-width: 767.98px) { .frontend-brand-name, .frontend-brand-home { display: none; } .frontend-topbar-inner { gap: .5rem; } .frontend-actions { gap: .25rem; } .frontend-admin-button, .frontend-logout-button { font-size: .75rem; padding: .45rem .6rem; } }
CSS); ?>

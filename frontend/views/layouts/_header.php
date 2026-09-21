<?php

declare(strict_types=1);

/** @var yii\web\View $this */

use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use common\models\ReceptionShift;
use yii\helpers\Html;

$backendBaseUrl = str_replace('/frontend/web', '/backend/web', rtrim(Yii::$app->request->baseUrl, '/'));
$identity = Yii::$app->user->identity;
$branch = (string) Yii::$app->session->get('pbz_branch', '');
$activeShift = null;
if ($identity !== null && $identity->isReception() && $branch !== '') {
    try {
        $activeShift = ReceptionShift::findOne([
            'user_id' => (int) $identity->id,
            'branch_code' => $branch,
            'status' => ReceptionShift::STATUS_OPEN,
        ]);
    } catch (Throwable $exception) {
        Yii::error($exception->getMessage(), __METHOD__);
    }
}
$deskReady = !Yii::$app->user->isGuest
    && $identity?->isReception() === true
    && $activeShift !== null;

$items = [
    [
        'label' => 'Home',
        'url' => ['/site/index'],
    ],
    [
        'label' => 'Check-In',
        'url' => ['/visitor/check-in'],
        'visible' => $deskReady,
    ],
    [
        'label' => 'Check-Out',
        'url' => ['/visitor/checkout-page'],
        'visible' => $deskReady,
    ],
    [
        'label' => 'Admin Panel',
        'url' => $backendBaseUrl . '/index.php/site/login',
        'linkOptions' => ['class' => 'btn btn-sm btn-warning ms-md-2'],
    ],
    [
        'label' => 'Logout (' . Html::encode(Yii::$app->user->identity?->username) . ')',
        'url' => ['/site/logout'],
        'linkOptions' => [
            'data-method' => 'post',
            'class' => 'logout',
        ],
        'visible' => !Yii::$app->user->isGuest,
    ],
];

?>
<header id="header">
    <?php NavBar::begin(
        [
            'brandLabel' => Html::tag('span', Html::img(Yii::getAlias('@web/images/pbz logo.png'), [
                'alt' => 'PBZ Bank Visitor Management System',
                'class' => 'brand-logo',
                'height' => 42,
            ]) . Html::tag('span', 'Visitor-Management-System', ['class' => 'frontend-brand-name']) . Html::tag('span', 'Visitor management', ['class' => 'frontend-brand-context']), ['class' => 'frontend-brand']),
            'brandUrl' => Yii::$app->homeUrl,
            'options' => ['class' => 'navbar-expand-md navbar-light bg-white fixed-top frontend-topbar'],
        ],
    ) ?>
    <?= Nav::widget(
        [
            'options' => ['class' => 'navbar-nav me-auto'],
            'encodeLabels' => false,
            'items' => $items,
        ],
    ) ?>
    <?= Html::button(
        '&#127769;',
        [
            'id' => 'theme-toggle',
            'class' => 'btn btn-link nav-link fs-5',
            'aria-label' => 'Switch to dark mode',
        ],
    ) ?>
    <?php NavBar::end() ?>
</header>
<?php $this->registerCss(<<<'CSS'
.frontend-topbar { border-bottom: 1px solid #dfe7ee; box-shadow: 0 2px 8px rgba(16,35,62,.06); min-height: 82px; }
.frontend-topbar .container { align-items: center; }
.frontend-brand { align-items: center; display: inline-flex; gap: .7rem; white-space: nowrap; }
.frontend-brand-name { color: #10233e; font-size: 1rem; font-weight: 800; }
.frontend-brand-context { border-left: 1px solid #dfe7ee; color: #6b7c8e; font-size: .78rem; padding-left: .7rem; }
.frontend-topbar .navbar-nav { align-items: center; gap: .2rem; }
.frontend-topbar .navbar-nav .nav-link { color: #53677c; font-weight: 600; padding: .55rem .7rem; }
.frontend-topbar .navbar-nav .nav-link:hover, .frontend-topbar .navbar-nav .nav-link:focus { color: #0d6efd; }
.frontend-topbar .navbar-nav .nav-link.logout { border: 1px solid #9aa9b7; border-radius: .35rem; margin-left: .3rem; padding: .45rem .7rem; }
.frontend-topbar .navbar-toggler { border-color: #9aa9b7; }
@media (max-width: 767.98px) { .frontend-brand-name, .frontend-brand-context { display: none; } .frontend-topbar .navbar-nav { align-items: stretch; padding-top: .5rem; } .frontend-topbar .navbar-nav .nav-link.logout { margin-left: 0; } }
CSS); ?>

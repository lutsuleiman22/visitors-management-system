<?php

declare(strict_types=1);

/** @var yii\web\View $this */

use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\helpers\Html;

$backendBaseUrl = str_replace('/frontend/web', '/backend/web', rtrim(Yii::$app->request->baseUrl, '/'));
$activeShift = Yii::$app->session->get('reception_shift', []);
$deskReady = !Yii::$app->user->isGuest
    && Yii::$app->user->identity?->isReception() === true
    && (string) Yii::$app->session->get('pbz_branch', '') !== ''
    && is_array($activeShift)
    && (int) ($activeShift['user_id'] ?? 0) === (int) Yii::$app->user->id
    && (string) ($activeShift['branch'] ?? '') === (string) Yii::$app->session->get('pbz_branch', '')
    && (string) ($activeShift['started_at'] ?? '') !== '';

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
            'brandLabel' => Html::img(
                Yii::getAlias('@web/images/pbz logo.png'),
                [
                    'alt' => 'PBZ Bank Visitor Management System',
                    'class' => 'brand-logo',
                    'height' => 42,
                ],
            ),
            'brandUrl' => Yii::$app->homeUrl,
            'options' => ['class' => 'navbar-expand-md navbar-dark bg-dark fixed-top'],
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

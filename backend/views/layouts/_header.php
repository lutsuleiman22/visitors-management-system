<?php

declare(strict_types=1);

/** @var yii\web\View $this */

use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\helpers\Html;
use common\services\NotificationService;

$identity = Yii::$app->user->identity;
$role = $identity === null ? '' : (string) $identity->role;
$isAdmin = $role === 'admin';
$isReception = $role === 'reception';
$isSecurity = $role === 'security';
$unreadNotifications = !Yii::$app->user->isGuest
    ? NotificationService::unreadCount((int) Yii::$app->user->id)
    : 0;

$items = [
    [
        'label' => $isAdmin ? 'Admin Dashboard' : ($isReception ? 'Reception Dashboard' : 'Security Dashboard'),
        'url' => $isAdmin ? ['/admin/dashboard'] : ($isReception ? ['/reception/dashboard'] : ['/security/dashboard']),
        'visible' => !Yii::$app->user->isGuest && ($isAdmin || $isReception || $isSecurity),
    ],
    [
        'label' => 'Visitor List',
        'url' => ['/visit/index'],
        'visible' => $isAdmin || $isReception,
    ],
    [
        'label' => 'Check-Out / Active Visitors',
        'url' => ['/visit/evacuation'],
        'visible' => $isAdmin || $isSecurity,
    ],
    [
        'label' => 'Users',
        'url' => ['/user/index'],
        'visible' => $isAdmin,
    ],
    [
        'label' => 'Reports',
        'url' => ['/admin/reports'],
        'visible' => $isAdmin,
    ],
    [
        'label' => 'Analytics',
        'url' => ['/dashboard/analytics'],
        'visible' => $isAdmin,
    ],
    [
        'label' => 'Add Visitor',
        'url' => ['/visit/create'],
        'visible' => $isReception || $isAdmin,
    ],
    [
        'label' => 'Active Visitors',
        'url' => ['/visit/evacuation'],
        'visible' => $isSecurity,
    ],
    [
        'label' => 'Notifications' . ($unreadNotifications > 0 ? ' <span class="badge text-bg-danger">' . $unreadNotifications . '</span>' : ''),
        'url' => ['/notification/index'],
        'encode' => false,
        'visible' => $isAdmin || $isReception || $isSecurity,
    ],
    [
        'label' => 'Login',
        'url' => ['/site/login'],
        'visible' => Yii::$app->user->isGuest,
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
            'brandLabel' => Yii::$app->name,
            'brandUrl' => Yii::$app->homeUrl,
            'options' => ['class' => 'navbar-expand-md navbar-dark bg-dark fixed-top']
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

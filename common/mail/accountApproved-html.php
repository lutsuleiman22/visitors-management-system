<?php

declare(strict_types=1);

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\User $user */
/** @var string $loginLink */
/** @var string $appName */
?>
<div class="account-approved">
    <p>Hello <?= Html::encode($user->username) ?>,</p>

    <p>Your <?= Html::encode(ucfirst((string) $user->role)) ?> account on <?= Html::encode($appName) ?> has been
        reviewed and approved by an administrator. You can now log in using the username and password you
        chose when you signed up.</p>

    <p><?= Html::a('Log in now', $loginLink) ?></p>

    <p>If the button above does not work, copy and paste this link into your browser:</p>
    <p><?= Html::encode($loginLink) ?></p>

    <p>If you did not request this account, please contact your administrator.</p>
</div>

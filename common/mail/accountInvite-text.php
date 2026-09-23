<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var common\models\User $user */
/** @var string $setPasswordLink */
/** @var string $appName */
?>
Hello <?= $user->username ?>,

An account has been created for you on <?= $appName ?> with the role of <?= ucfirst((string) $user->role) ?>.

Please set your password to activate your account by visiting the link below:

<?= $setPasswordLink ?>

This link will expire in <?= (int) (Yii::$app->params['user.passwordResetTokenExpire'] / 3600) ?> hour(s).
If you did not expect this email, please contact your administrator.

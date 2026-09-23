<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var common\models\User $user */
/** @var string $loginLink */
/** @var string $appName */
?>
Hello <?= $user->username ?>,

Your <?= ucfirst((string) $user->role) ?> account on <?= $appName ?> has been reviewed and approved by an
administrator. You can now log in using the username and password you chose when you signed up:

<?= $loginLink ?>

If you did not request this account, please contact your administrator.

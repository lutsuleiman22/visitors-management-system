<?php

declare(strict_types=1);

/** @var yii\web\View $this */

use yii\helpers\Html;
use common\models\Notification;
use common\services\NotificationService;

$identity = Yii::$app->user->identity;
$role = $identity === null ? '' : (string) $identity->role;
$isAdmin = $role === 'admin';
$isReception = $role === 'reception';
$isSecurity = $role === 'security';
$frontendBaseUrl = str_replace('/backend/web', '/frontend/web', rtrim(Yii::$app->request->baseUrl, '/'));
$unreadNotifications = !Yii::$app->user->isGuest
    ? NotificationService::unreadCount((int) Yii::$app->user->id)
    : 0;
$latestNotifications = [];
if (!Yii::$app->user->isGuest && ($isAdmin || $isReception || $isSecurity)) {
    try {
        $latestNotifications = Notification::find()
            ->alias('n')
            ->where(['or', ['n.user_id' => (int) Yii::$app->user->id], ['n.user_id' => null]])
            ->orderBy(['n.created_at' => SORT_DESC, 'n.id' => SORT_DESC])
            ->limit(5)
            ->asArray()
            ->all();
    } catch (Throwable $exception) {
        Yii::error($exception->getMessage(), __METHOD__);
    }
}

?>
<header id="header" class="backend-topbar">
    <div class="topbar-left">
        <?= Html::button('☰', ['id' => 'sidebar-toggle', 'class' => 'btn btn-sm btn-outline-secondary', 'aria-label' => 'Toggle navigation']) ?>
        <?= Html::a('Visitor-Management-System', ['/site/index'], ['class' => 'topbar-brand']) ?>
        <span class="topbar-context">Visitor management</span>
    </div>
    <div class="topbar-actions">
        <?php if (!Yii::$app->user->isGuest && $role !== ''): ?><span class="badge rounded-pill text-bg-light border topbar-role"><?= Html::encode(strtoupper($role)) ?></span><?php endif; ?>
        <?php if (!Yii::$app->user->isGuest && ($isAdmin || $isReception || $isSecurity)): ?>
            <div class="dropdown">
                <button type="button" class="btn btn-sm btn-light position-relative" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">Bell<?php if ($unreadNotifications > 0): ?><span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger"><?= (int) $unreadNotifications ?></span><?php endif; ?></button>
                <div class="dropdown-menu dropdown-menu-end shadow-sm border-0 p-2" style="min-width: 300px;">
                    <div class="d-flex justify-content-between align-items-center px-2 py-1"><strong>Notifications</strong><?= Html::a('View all', ['/notification/index'], ['class' => 'small text-decoration-none']) ?></div>
                    <?php if ($latestNotifications === []): ?>
                        <div class="small text-body-secondary px-2 py-3">No notifications found.</div>
                    <?php else: foreach ($latestNotifications as $notification): ?>
                        <div class="dropdown-item-text px-2 py-2 border-top"><strong class="small"><?= Html::encode($notification['title'] ?? 'Notification') ?></strong><div class="small text-body-secondary text-truncate"><?= Html::encode($notification['message'] ?? '') ?></div></div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        <?php endif; ?>
        <?= Html::button('Theme', ['id' => 'theme-toggle', 'class' => 'btn btn-sm btn-outline-secondary', 'aria-label' => 'Switch color theme']) ?>
        <?= Html::a('Visitor Site', $frontendBaseUrl . '/index.php/site/index', ['class' => 'btn btn-sm btn-warning']) ?>
        <?php if (Yii::$app->user->isGuest): ?>
            <?= Html::a('Sign in', ['/site/login'], ['class' => 'btn btn-sm btn-primary']) ?>
        <?php else: ?>
            <?= Html::beginForm(['/site/logout'], 'post', ['class' => 'd-inline']) ?>
            <?= Html::submitButton('Sign out', ['class' => 'btn btn-sm btn-outline-secondary']) ?>
            <?= Html::endForm() ?>
        <?php endif; ?>
    </div>
</header>
<?php $this->registerJs(<<<'JS'
(function () {
    const toggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('backend-sidebar');
    const frame = document.querySelector('.backend-frame');
    if (toggle && sidebar && frame) {
        toggle.addEventListener('click', function () {
            if (window.matchMedia('(max-width: 767.98px)').matches) {
                sidebar.classList.toggle('is-open');
            } else {
                sidebar.classList.toggle('collapsed');
                frame.classList.toggle('sidebar-collapsed');
            }
        });
    }
})();
JS); ?>

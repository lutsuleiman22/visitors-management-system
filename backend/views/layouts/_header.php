<?php

declare(strict_types=1);

/** @var yii\web\View $this */

use yii\helpers\Html;

$identity = Yii::$app->user->identity;
$role = $identity === null ? '' : (string) $identity->role;
$isAdmin = $role === 'admin';
$isReception = $role === 'reception';
$frontendBaseUrl = str_replace('/backend/web', '/frontend/web', rtrim(Yii::$app->request->baseUrl, '/'));

?>
<header id="header" class="backend-topbar">
    <div class="topbar-left">
        <?= Html::button('☰', ['id' => 'sidebar-toggle', 'class' => 'btn btn-sm btn-outline-secondary', 'aria-label' => 'Toggle navigation']) ?>
        <?= Html::a('Visitor-Management-System', ['/site/index'], ['class' => 'topbar-brand']) ?>
        <span class="topbar-context">Visitor management</span>
    </div>
    <div class="topbar-actions">
        <?php if (!Yii::$app->user->isGuest && $role !== ''): ?><span class="badge rounded-pill text-bg-light border topbar-role"><?= Html::encode(strtoupper($role)) ?></span><?php endif; ?>
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

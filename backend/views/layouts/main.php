<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var string $content */

use common\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\helpers\Html;

$identity = Yii::$app->user->identity;
$role = $identity === null ? '' : strtolower((string) $identity->role);
$isAdmin = $role === 'admin';
$isReception = $role === 'reception';
$isSecurity = $role === 'security';

$this->render('_head');
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100" data-bs-theme="light">
<head>
    <?php $this->head() ?>
    <title><?= Html::encode($this->title) ?></title>
</head>
<body class="d-flex flex-column min-vh-100 backend-shell">
<?php $this->beginBody() ?>

<?= $this->render('_header') ?>

<div class="backend-frame">
    <aside id="backend-sidebar" class="backend-sidebar" aria-label="Primary navigation">
        <div class="sidebar-brand">
            <span class="sidebar-mark">VM</span>
            <div><strong>Visitor Desk</strong><small>Operations portal</small></div>
        </div>
        <div class="sidebar-section-label">Workspace</div>
        <nav class="sidebar-nav">
            <?php if ($isAdmin): ?>
                <a class="sidebar-link" href="<?= Html::encode(Yii::$app->urlManager->createUrl(['/admin/dashboard'])) ?>"><span class="sidebar-icon">D</span>Admin Dashboard</a>
                <a class="sidebar-link" href="<?= Html::encode(Yii::$app->urlManager->createUrl(['/user/index'])) ?>"><span class="sidebar-icon">U</span>User Management</a>
                <a class="sidebar-link" href="<?= Html::encode(Yii::$app->urlManager->createUrl(['/admin/reports'])) ?>"><span class="sidebar-icon">R</span>Reports</a>
                <a class="sidebar-link" href="<?= Html::encode(Yii::$app->urlManager->createUrl(['/dashboard/analytics'])) ?>"><span class="sidebar-icon">A</span>Analytics</a>
                <a class="sidebar-link" href="<?= Html::encode(Yii::$app->urlManager->createUrl(['/visit/index'])) ?>"><span class="sidebar-icon">V</span>Visitors</a>
                <a class="sidebar-link" href="<?= Html::encode(Yii::$app->urlManager->createUrl(['/visit/evacuation'])) ?>"><span class="sidebar-icon">E</span>Active Visits</a>
                <a class="sidebar-link" href="<?= Html::encode(Yii::$app->urlManager->createUrl(['/notification/index'])) ?>"><span class="sidebar-icon">N</span>Notifications</a>
                <a class="sidebar-link" href="<?= Html::encode(Yii::$app->urlManager->createUrl(['/audit-log/index'])) ?>"><span class="sidebar-icon">A</span>Audit Logs</a>
            <?php elseif ($isReception): ?>
                <a class="sidebar-link" href="<?= Html::encode(Yii::$app->urlManager->createUrl(['/reception/dashboard'])) ?>"><span class="sidebar-icon">D</span>Dashboard</a>
                <a class="sidebar-link" href="<?= Html::encode(Yii::$app->urlManager->createUrl(['/visit/create'])) ?>"><span class="sidebar-icon">+</span>Add Visitor</a>
                <a class="sidebar-link" href="<?= Html::encode(Yii::$app->urlManager->createUrl(['/visit/index'])) ?>"><span class="sidebar-icon">V</span>Visitor List</a>
                <a class="sidebar-link" href="<?= Html::encode(Yii::$app->urlManager->createUrl(['/visit/index'])) ?>"><span class="sidebar-icon">C</span>Check-in / Check-out</a>
                <a class="sidebar-link" href="<?= Html::encode(Yii::$app->urlManager->createUrl(['/notification/index'])) ?>"><span class="sidebar-icon">N</span>Notifications</a>
            <?php elseif ($isSecurity): ?>
                <a class="sidebar-link" href="<?= Html::encode(Yii::$app->urlManager->createUrl(['/security/dashboard'])) ?>"><span class="sidebar-icon">D</span>Dashboard</a>
                <a class="sidebar-link" href="<?= Html::encode(Yii::$app->urlManager->createUrl(['/visit/evacuation'])) ?>"><span class="sidebar-icon">A</span>Active Visitors</a>
                <a class="sidebar-link" href="<?= Html::encode(Yii::$app->urlManager->createUrl(['/notification/index'])) ?>"><span class="sidebar-icon">M</span>Monitoring</a>
            <?php endif; ?>
        </nav>
        <?php if (!Yii::$app->user->isGuest): ?>
            <div class="sidebar-user mt-auto">
                <div class="sidebar-user-avatar"><?= Html::encode(strtoupper(substr((string) $identity->username, 0, 1))) ?></div>
                <div class="text-truncate"><strong><?= Html::encode($identity->username) ?></strong><small><?= Html::encode(ucfirst($role)) ?></small></div>
            </div>
            <?= Html::beginForm(['/site/logout'], 'post', ['class' => 'mt-2']) ?>
            <?= Html::submitButton('Sign out', ['class' => 'sidebar-link w-100 border-0 bg-transparent text-start']) ?>
            <?= Html::endForm() ?>
        <?php endif; ?>
    </aside>

    <div class="backend-main">
        <main id="main" class="backend-content" role="main">
            <div class="container-fluid px-3 px-lg-4">
                <?php if (!empty($this->params['breadcrumbs'])): ?>
                    <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs'], 'options' => ['class' => 'breadcrumb mb-3']]) ?>
                <?php endif ?>
                <?= Alert::widget() ?>
                <?= $content ?>
            </div>
        </main>
        <?= $this->render('_footer') ?>
    </div>
</div>

<?php $this->registerCss(<<<'CSS'
:root { --backend-sidebar: 248px; --backend-ink: #17212b; --backend-muted: #667482; --backend-line: #e5e9ed; --backend-surface: #ffffff; --backend-canvas: #f4f7f9; }
.backend-shell { background: var(--backend-canvas); color: var(--backend-ink); }
.backend-topbar { position: fixed; z-index: 1040; inset: 0 0 auto 0; height: 64px; display: flex; align-items: center; justify-content: space-between; padding: 0 1.25rem; background: var(--backend-surface); border-bottom: 1px solid var(--backend-line); }
.topbar-left, .topbar-actions { display: flex; align-items: center; gap: .8rem; }
.topbar-brand { color: var(--backend-ink); font-weight: 800; letter-spacing: .01em; text-decoration: none; }
.topbar-context { color: var(--backend-muted); font-size: .82rem; border-left: 1px solid var(--backend-line); padding-left: .8rem; }
.topbar-link { color: var(--backend-muted); font-size: .85rem; text-decoration: none; }
.topbar-link:hover { color: var(--bs-primary); }
.topbar-role { color: var(--backend-muted); font-size: .68rem; letter-spacing: .06em; }
.backend-frame { display: flex; flex: 1; min-height: 100vh; padding-top: 64px; }
.backend-sidebar { position: fixed; z-index: 1030; inset: 64px auto 0 0; width: var(--backend-sidebar); display: flex; flex-direction: column; padding: 1.25rem .85rem 1rem; background: #17212b; color: #d8e0e7; }
.sidebar-brand { display: flex; align-items: center; gap: .7rem; padding: .35rem .65rem 1.35rem; color: #fff; }
.sidebar-brand small, .sidebar-user small { display: block; color: #93a4b2; font-size: .72rem; margin-top: .15rem; }
.sidebar-mark { display: grid; place-items: center; width: 2.15rem; height: 2.15rem; border-radius: .65rem; background: #2d9c8a; color: #fff; font-size: .75rem; font-weight: 800; letter-spacing: .04em; }
.sidebar-section-label { padding: .5rem .7rem; color: #8293a1; font-size: .68rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; }
.sidebar-nav { display: grid; gap: .25rem; }
.sidebar-link { display: flex; align-items: center; gap: .7rem; padding: .7rem .7rem; border-radius: .55rem; color: #c7d1d8; font-size: .9rem; text-decoration: none; transition: background .15s ease, color .15s ease; }
.sidebar-link:hover, .sidebar-link:focus { background: rgba(255,255,255,.1); color: #fff; }
.sidebar-icon { display: grid; place-items: center; flex: 0 0 1.55rem; height: 1.55rem; border: 1px solid rgba(255,255,255,.2); border-radius: .4rem; color: #8bd3c5; font-size: .72rem; font-weight: 800; }
.sidebar-user { display: flex; align-items: center; gap: .65rem; margin: 1rem .25rem 0; padding: .75rem .45rem 0; border-top: 1px solid rgba(255,255,255,.12); font-size: .82rem; }
.sidebar-user-avatar { display: grid; place-items: center; flex: 0 0 2rem; height: 2rem; border-radius: 50%; background: #d98d4c; color: #fff; font-weight: 700; }
.backend-main { display: flex; flex: 1; min-width: 0; flex-direction: column; margin-left: var(--backend-sidebar); }
.backend-content { flex: 1; padding: 1.5rem 0 2.5rem; }
.backend-content .card { border: 1px solid var(--backend-line) !important; border-radius: .7rem; box-shadow: 0 4px 18px rgba(23,33,43,.04) !important; }
.backend-content .table { --bs-table-bg: var(--backend-surface); }
.backend-content .table thead th { color: var(--backend-muted); font-size: .72rem; letter-spacing: .06em; text-transform: uppercase; background: #f8fafb; border-bottom-width: 1px; }
.backend-content .table td, .backend-content .table th { padding: .8rem .9rem; }
@media (max-width: 991.98px) { .backend-sidebar { width: 210px; } .backend-main { margin-left: 210px; } }
@media (max-width: 767.98px) { .backend-topbar { height: 58px; padding: 0 .85rem; } .topbar-context { display: none; } .topbar-actions { gap: .35rem; } .topbar-link { font-size: 0; } .topbar-link .badge { font-size: .7rem; } .backend-frame { padding-top: 58px; } .backend-sidebar { inset: 58px auto 0 -248px; width: 248px; transition: transform .2s ease; } .backend-sidebar.is-open { transform: translateX(248px); } .backend-main { margin-left: 0; } .backend-content { padding-top: 1rem; } }
CSS); ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage();

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
<?php $this->registerCss(<<<'CSS'
body.backend-shell { position: relative; }
body.backend-shell::before { background: url('/visitors-management-system/frontend/web/images/pbz%20images.png') center / min(42vw, 520px) no-repeat; content: ''; inset: 0; opacity: .06; pointer-events: none; position: fixed; z-index: 0; }
body.backend-shell > * { position: relative; z-index: 1; }
CSS); ?>

<?= $this->render('_header') ?>

<div class="backend-frame">
    <aside id="backend-sidebar" class="backend-sidebar" aria-label="Primary navigation">
        <div class="sidebar-brand">
            <span class="sidebar-mark">VM</span>
            <div><strong>Visitor-Management-System</strong><small>Operations portal</small></div>
        </div>
        <div class="sidebar-section-label">Workspace</div>
        <nav class="sidebar-nav">
            <?php if ($isAdmin): ?>
                <a class="sidebar-link" href="<?= Html::encode(Yii::$app->urlManager->createUrl(['/admin/dashboard'])) ?>"><span class="sidebar-icon">D</span><span class="menu-text">Admin Dashboard</span></a>
                <a class="sidebar-link" href="<?= Html::encode(Yii::$app->urlManager->createUrl(['/admin/branches'])) ?>"><span class="sidebar-icon">B</span><span class="menu-text">Branches </span></a>
                <a class="sidebar-link" href="<?= Html::encode(Yii::$app->urlManager->createUrl(['/user/index'])) ?>"><span class="sidebar-icon">U</span><span class="menu-text">User Management</span></a>
                <a class="sidebar-link" href="<?= Html::encode(Yii::$app->urlManager->createUrl(['/admin/reports'])) ?>"><span class="sidebar-icon">R</span><span class="menu-text">Reports</span></a>
                <a class="sidebar-link" href="<?= Html::encode(Yii::$app->urlManager->createUrl(['/dashboard/analytics'])) ?>"><span class="sidebar-icon">A</span><span class="menu-text">Analytics</span></a>
                <a class="sidebar-link" href="<?= Html::encode(Yii::$app->urlManager->createUrl(['/visit/index'])) ?>"><span class="sidebar-icon">V</span><span class="menu-text">Visitors</span></a>
                <a class="sidebar-link" href="<?= Html::encode(Yii::$app->urlManager->createUrl(['/audit-log/index'])) ?>"><span class="sidebar-icon">A</span><span class="menu-text">Audit Logs</span></a>
                <a class="sidebar-link" href="<?= Html::encode(Yii::$app->urlManager->createUrl(['/admin/shifts'])) ?>"><span class="sidebar-icon">S</span><span class="menu-text">Shift Registration</span></a>
            <?php elseif ($isReception): ?>
                <a class="sidebar-link" href="<?= Html::encode(Yii::$app->urlManager->createUrl(['/reception/dashboard'])) ?>"><span class="sidebar-icon">D</span><span class="menu-text">Dashboard</span></a>
                <a class="sidebar-link" href="<?= Html::encode(Yii::$app->urlManager->createUrl(['/visit/create'])) ?>"><span class="sidebar-icon">+</span><span class="menu-text">Add Visitor</span></a>
                <a class="sidebar-link" href="<?= Html::encode(Yii::$app->urlManager->createUrl(['/visit/index'])) ?>"><span class="sidebar-icon">V</span><span class="menu-text">Visitor List</span></a>
                <a class="sidebar-link" href="<?= Html::encode(Yii::$app->urlManager->createUrl(['/visit/index'])) ?>"><span class="sidebar-icon">C</span><span class="menu-text">Check-in / Check-out</span></a>
            <?php endif; ?>
        </nav>
        <?php if (!Yii::$app->user->isGuest): ?>
            <div class="sidebar-user mt-auto">
                <div class="sidebar-user-avatar"><?= Html::encode(strtoupper(substr((string) $identity->username, 0, 1))) ?></div>
                <div class="text-truncate"><strong><?= Html::encode($identity->username) ?></strong><small><?= Html::encode(ucfirst($role)) ?></small></div>
            </div>
            <?= Html::beginForm(['/site/logout'], 'post', ['class' => 'mt-2']) ?>
            <?= Html::submitButton('<span class="menu-text">Sign out</span>', ['class' => 'sidebar-link w-100 border-0 bg-transparent text-start', 'encode' => false]) ?>
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
:root { --backend-sidebar: 248px; --backend-sidebar-collapsed: 70px; --backend-ink: #17212b; --backend-muted: #667482; --backend-line: #e5e9ed; --backend-surface: #ffffff; --backend-canvas: #f4f7f9; }
:root { --backend-sidebar: 248px; --backend-sidebar-collapsed: 70px; --backend-ink: #10233e; --backend-muted: #5b6f82; --backend-line: #dfe7ee; --backend-surface: #ffffff; --backend-canvas: #eef4f7; --bank-navy: #10233e; --bank-blue: #1a4c74; --bank-teal: #1fa7a1; --bank-gold: #d4a94d; }
.backend-shell { background: var(--backend-canvas); color: var(--backend-ink); }
body { overflow-y: auto; }
.backend-topbar { position: sticky; top: 0; z-index: 2000; height: 64px; display: flex; align-items: center; justify-content: space-between; padding: 0 1.25rem; background: linear-gradient(135deg, #f8fafc 0%, #edf5f7 100%); border-bottom: 1px solid var(--backend-line); box-shadow: 0 1px 0 rgba(16, 35, 62, 0.06); }
.topbar-left, .topbar-actions { display: flex; align-items: center; gap: .8rem; }
.topbar-brand { color: var(--bank-navy); font-weight: 800; letter-spacing: .01em; text-decoration: none; }
.topbar-context { color: var(--backend-muted); font-size: .82rem; border-left: 1px solid var(--backend-line); padding-left: .8rem; }
.topbar-link { color: var(--backend-muted); font-size: .85rem; text-decoration: none; }
.topbar-link:hover { color: var(--bank-blue); }
.topbar-role { color: var(--bank-navy); font-size: .68rem; letter-spacing: .06em; }
.backend-frame { display: flex; flex: 1; min-height: calc(100vh - 64px); }
.backend-sidebar { position: sticky; top: 64px; left: 0; z-index: 1500; height: calc(100vh - 64px); width: var(--backend-sidebar); display: flex; flex-direction: column; overflow-x: hidden; overflow-y: auto; padding: 1.25rem .85rem 1rem; background: linear-gradient(180deg, #10233e 0%, #143755 100%); color: #d8e0e7; transition: width .2s ease; }
.sidebar-brand { display: flex; align-items: center; gap: .7rem; padding: .35rem .65rem 1.35rem; color: #fff; }
.sidebar-brand small, .sidebar-user small { display: block; color: #a7bac9; font-size: .72rem; margin-top: .15rem; }
.sidebar-mark { display: grid; place-items: center; width: 2.15rem; height: 2.15rem; border-radius: .65rem; background: linear-gradient(135deg, var(--bank-gold), #b7872d); color: #fff; font-size: .75rem; font-weight: 800; letter-spacing: .04em; }
.sidebar-section-label { padding: .5rem .7rem; color: #9ab2c8; font-size: .68rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; }
.sidebar-nav { display: grid; gap: .25rem; }
.sidebar-link { display: flex; align-items: center; gap: .7rem; padding: .7rem .7rem; border-radius: .55rem; color: #dfeaf6; font-size: .9rem; text-decoration: none; transition: background .15s ease, color .15s ease; }
.sidebar-link:hover, .sidebar-link:focus { background: rgba(255,255,255,.08); color: #fff; }
.sidebar-icon { display: grid; place-items: center; flex: 0 0 1.55rem; height: 1.55rem; border: 1px solid rgba(255,255,255,.18); border-radius: .4rem; color: #6ee0d3; font-size: .72rem; font-weight: 800; }
.sidebar-user { display: flex; align-items: center; gap: .65rem; margin: 1rem .25rem 0; padding: .75rem .45rem 0; border-top: 1px solid rgba(255,255,255,.12); font-size: .82rem; }
.sidebar-user-avatar { display: grid; place-items: center; flex: 0 0 2rem; height: 2rem; border-radius: 50%; background: linear-gradient(135deg, var(--bank-gold), #b7872d); color: #fff; font-weight: 700; }
.backend-main { display: flex; flex: 1; min-width: 0; flex-direction: column; margin-left: 0; transition: margin-left .2s ease; position: relative; z-index: 1; }
.backend-sidebar.collapsed { width: var(--backend-sidebar-collapsed); }
.backend-frame.sidebar-collapsed .backend-main { margin-left: 0; }
.backend-sidebar.collapsed .menu-text, .backend-sidebar.collapsed .sidebar-brand > div, .backend-sidebar.collapsed .sidebar-section-label, .backend-sidebar.collapsed .sidebar-user > div:not(.sidebar-user-avatar) { display: none; }
.backend-sidebar.collapsed .sidebar-link { justify-content: center; padding-left: .7rem; padding-right: .7rem; }
.backend-sidebar.collapsed .sidebar-brand { justify-content: center; padding-left: 0; padding-right: 0; }
.backend-sidebar.collapsed .sidebar-user { justify-content: center; }
.backend-content { flex: 1; padding: 1.5rem 0 2.5rem; }
.backend-content .card { border: 1px solid var(--backend-line) !important; border-radius: .7rem; box-shadow: 0 4px 18px rgba(23,33,43,.04) !important; }
.backend-content .table { --bs-table-bg: var(--backend-surface); }
.backend-content .table thead th { color: var(--backend-muted); font-size: .72rem; letter-spacing: .06em; text-transform: uppercase; background: #f8fafb; border-bottom-width: 1px; }
.backend-content .table td, .backend-content .table th { padding: .8rem .9rem; }
@media (max-width: 991.98px) and (min-width: 768px) { .backend-sidebar { width: 210px; } .backend-main { margin-left: 0; } .backend-frame.sidebar-collapsed .backend-main { margin-left: 0; } }
@media (max-width: 767.98px) { .backend-topbar { height: 58px; padding: 0 .85rem; } .topbar-context { display: none; } .topbar-actions { gap: .35rem; } .topbar-link { font-size: 0; } .topbar-link .badge { font-size: .7rem; } .backend-frame { min-height: calc(100vh - 58px); } .backend-sidebar { top: 58px; height: calc(100vh - 58px); left: -248px; width: 248px; transition: transform .2s ease; } .backend-sidebar.is-open { transform: translateX(248px); } .backend-main, .backend-frame.sidebar-collapsed .backend-main { margin-left: 0; } .backend-content { padding-top: 1rem; } }
CSS); ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage();

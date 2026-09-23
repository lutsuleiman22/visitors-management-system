<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var string $content */

use common\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\helpers\Html;

$this->render('_head');
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100" data-bs-theme="light">
<head>
    <?php $this->head() ?>
    <title><?= Html::encode($this->title) ?></title>
</head>
<body class="d-flex flex-column h-100 frontend-shell">
<?php $this->beginBody() ?>
<?php $this->registerCss(<<<'CSS'
body.frontend-shell { position: relative; }
body.frontend-shell::before { background: url('/visitors-management-system/frontend/web/images/pbz%20images.png') center / min(42vw, 520px) no-repeat; content: ''; inset: 0; opacity: .06; pointer-events: none; position: fixed; z-index: 0; }
body.frontend-shell > * { position: relative; z-index: 1; }
main .breadcrumb, .breadcrumb { display: none !important; }
CSS); ?>

<?= $this->render('_header') ?>

<main id="main" class="flex-grow-1" role="main">
    <div class="container">
        <?php if (!empty($this->params['breadcrumbs'])): ?>
            <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
        <?php endif ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</main>

<?= $this->render('_footer') ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>

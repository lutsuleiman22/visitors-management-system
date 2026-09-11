<?php

declare(strict_types=1);

use yii\helpers\Html;

$this->title = 'Reports';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="reports-page">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <div class="reports-eyebrow">Management intelligence</div>
            <h1 class="h2 mb-1">Reports</h1>
            <p class="text-body-secondary mb-0">Choose a reporting period to review visitor activity.</p>
        </div>
        <div class="d-flex gap-2">
            <?= Html::a('🏠 Dashboard', ['/admin/dashboard'], ['class' => 'btn btn-warning']) ?>
        </div>
    </div>
    <div class="row g-3">
        <?php foreach ([
            ['Daily Reports', 'Today\'s visitor activity', 'daily', 'D'],
            ['Weekly Reports', 'Visitor activity from the last 7 days', 'weekly', 'W'],
            ['Monthly Reports', 'Visitor activity from the last 30 days', 'monthly', 'M'],
            ['Annual Reports', 'Visitor activity from the last 365 days', 'annual', 'Y'],
        ] as [$label, $description, $route, $mark]): ?>
            <div class="col-sm-6 col-xl-3">
                <a class="report-period-card" href="<?= Html::encode(Yii::$app->urlManager->createUrl(['/admin/' . $route])) ?>">
                    <span class="report-period-mark"><?= Html::encode($mark) ?></span>
                    <h2><?= Html::encode($label) ?></h2>
                    <p><?= Html::encode($description) ?></p>
                    <span class="report-period-link">Open report &rarr;</span>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php $this->registerCss(<<<'CSS'
.reports-page { --reports-yellow: #1fa651; --reports-ink: #1a1a1a; --reports-border: #eeeeee; color: var(--reports-ink); }
.reports-eyebrow { color: #6b6b6b; font-size: .72rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
.report-period-card { background: #fff; border: 1px solid var(--reports-border); color: var(--reports-ink); display: block; height: 100%; padding: 1.35rem; text-decoration: none; }
.report-period-card:hover { border-color: var(--reports-yellow); color: var(--reports-ink); }
.report-period-mark { align-items: center; background: var(--reports-yellow); display: inline-flex; font-size: .8rem; font-weight: 900; height: 2.25rem; justify-content: center; margin-bottom: 2rem; width: 2.25rem; }
.report-period-card h2 { font-size: 1.05rem; font-weight: 800; margin-bottom: .45rem; }
.report-period-card p { color: #777; font-size: .82rem; min-height: 2.5rem; }
.report-period-link { color: #0d6efd; font-size: .8rem; font-weight: 800; }
CSS); ?>

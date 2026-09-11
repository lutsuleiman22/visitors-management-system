<?php

declare(strict_types=1);

/** @var string $title */
/** @var common\models\Visit[] $visits */
/** @var string $filterType */
/** @var string $filterValue */
/** @var DateTimeImmutable $startDate */
/** @var DateTimeImmutable $endDate */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = $title;
$this->params['breadcrumbs'][] = ['label' => 'Reports', 'url' => ['/admin/reports']];
$this->params['breadcrumbs'][] = $title;
$exportQuery = ['type' => $filterType];
if ($filterType === 'weekly') {
    $exportQuery['date'] = $filterValue;
} elseif ($filterType === 'monthly') {
    $exportQuery['month'] = $filterValue;
} elseif ($filterType === 'annual') {
    $exportQuery['year'] = $filterValue;
}
$pdfUrl = Url::to(array_merge(['/admin/export-pdf'], $exportQuery));
$excelUrl = Url::to(array_merge(['/admin/export-excel'], $exportQuery));
?>
<div id="print-report" class="time-report-page">
    <div class="report-toolbar mb-3 d-flex flex-wrap gap-2">
        <button type="button" onclick="window.print()" class="btn btn-dark">&#128438; Print Report</button>
        <a href="<?= Html::encode($pdfUrl) ?>" class="btn btn-danger">&#128196; Download PDF</a>
        <a href="<?= Html::encode($excelUrl) ?>" class="btn btn-success">&#128202; Download Excel</a>
    </div>
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <div class="reports-eyebrow">Management intelligence</div>
            <h1 class="h2 mb-1"><?= Html::encode($title) ?></h1>
            <p class="text-body-secondary mb-0">Records created from <?= Html::encode($startDate->format('Y-m-d')) ?> to <?= Html::encode($endDate->format('Y-m-d')) ?>.</p>
        </div>
    </div>
    <?php if ($filterType !== 'daily'): ?>
        <?= Html::beginForm(['/admin/' . $filterType], 'get', ['class' => 'report-filter mb-4']) ?>
            <?php if ($filterType === 'weekly'): ?>
                <?= Html::label('Select Date', 'report-date', ['class' => 'form-label']) ?>
                <?= Html::input('date', 'date', $filterValue, ['id' => 'report-date', 'max' => date('Y-m-d'), 'class' => 'form-control']) ?>
            <?php elseif ($filterType === 'monthly'): ?>
                <?= Html::label('Select Month', 'report-month', ['class' => 'form-label']) ?>
                <?= Html::input('month', 'month', $filterValue, ['id' => 'report-month', 'max' => date('Y-m'), 'class' => 'form-control']) ?>
            <?php else: ?>
                <?= Html::label('Select Year', 'report-year', ['class' => 'form-label']) ?>
                <?= Html::input('number', 'year', $filterValue, ['id' => 'report-year', 'min' => '1970', 'max' => date('Y'), 'step' => '1', 'class' => 'form-control']) ?>
            <?php endif; ?>
            <?= Html::submitButton('Apply Filter', ['class' => 'btn btn-warning align-self-end']) ?>
        <?= Html::endForm() ?>
    <?php endif; ?>
    <section class="report-table-panel">
        <div class="report-table-heading"><div><h2>Visitor activity</h2><p><?= count($visits) ?> matching records</p></div></div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Name</th><th>Host</th><th>Status</th><th>Check-in time</th><th>Created</th></tr></thead>
                <tbody>
                <?php if ($visits === []): ?>
                    <tr><td colspan="5" class="text-center text-body-secondary py-5">No records found for this period.</td></tr>
                <?php else: foreach ($visits as $visit): ?>
                    <?php $inside = $visit->isCheckedIn(); ?>
                    <tr>
                        <td><strong><?= Html::encode($visit->visitor?->full_name ?? 'Unknown visitor') ?></strong></td>
                        <td><?= Html::encode($visit->host?->username ?? 'Unassigned') ?></td>
                        <td><span class="report-status report-status--<?= $inside ? 'inside' : 'out' ?>"><?= $inside ? 'Inside' : 'Checked out' ?></span></td>
                        <td class="text-body-secondary"><?= Html::encode($visit->check_in_time ?: '—') ?></td>
                        <td class="text-body-secondary"><?= $visit->created_at ? Html::encode(date('Y-m-d H:i', (int) $visit->created_at)) : '—' ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
<?php $this->registerCss(<<<'CSS'
.time-report-page { --reports-yellow: #fff200; --reports-ink: #da6868; --reports-border: #6f57c6; color: var(--reports-ink); }
.reports-eyebrow { color: #6b6b6b; font-size: .72rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
.report-table-panel { background: #38cb58; border: 1px solid var(--reports-border); }
.report-table-heading { padding: 1.15rem; }
.report-table-heading h2 { font-size: 1rem; font-weight: 800; margin: 0; }
.report-table-heading p { color: #777; font-size: .8rem; margin: .25rem 0 0; }
.report-table-panel thead th { background: #a1be44; border-bottom: 1px solid var(--reports-border); color: #777; font-size: .68rem; letter-spacing: .06em; padding: .75rem 1.15rem; text-transform: uppercase; }
.report-table-panel tbody td { border-color: var(--reports-border); padding: .8rem 1.15rem; }
.report-status { display: inline-block; font-size: .72rem; font-weight: 800; padding: .3rem .5rem; }
.report-status--inside { background: #c73396; color: #167345; }
.report-status--out { background: #49a6e3; color: #555; }
.report-filter { align-items: end; background: #3bdd61; border: 1px solid var(--reports-border); display: flex; gap: .75rem; padding: 1rem 1.15rem; }
.report-filter .form-label { font-size: .78rem; font-weight: 700; margin-bottom: .35rem; }
.report-filter .form-control { max-width: 250px; }
@media print {
    body * { visibility: hidden !important; }
    #print-report, #print-report * { visibility: visible !important; }
    #print-report { border: 0; left: 0; position: absolute; top: 0; width: 100%; }
    .report-toolbar, .report-filter { display: none !important; }
}
CSS); ?>

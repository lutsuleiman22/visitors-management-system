<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var common\models\Visit[] $visits */

use yii\helpers\Html;

$this->title = 'Visitor Check-Out';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="visitor-checkout-page">
    <div class="visitor-checkout-shell">
        <div class="visitor-checkout-card">
            <div class="visitor-checkout-heading">
                <span class="visitor-checkout-mark">OUT</span>
                <div>
                    <span class="visitor-checkout-eyebrow">Self-service exit</span>
                    <h1>Visitor Check-Out</h1>
                    <p>Find your name in today's visitor list to complete checkout.</p>
                </div>
            </div>
            <div class="visitor-checkout-body">
                <label for="visitor-search" class="form-label">Search your name</label>
                <input type="search" id="visitor-search" class="form-control visitor-search" placeholder="Type your name or host" autocomplete="off">

                <label for="visitor-select" class="form-label mt-3">Select your visit</label>
                <select id="visitor-select" class="form-select visitor-select" size="<?= min(6, max(3, count($visits))) ?>">
                    <option value="">Choose your name</option>
                    <?php foreach ($visits as $visit): ?>
                        <option value="<?= (int) $visit->id ?>" data-name="<?= Html::encode($visit->visitor?->full_name ?? '') ?>" data-host="<?= Html::encode($visit->host?->username ?? 'Unassigned') ?>" data-check-in="<?= Html::encode($visit->check_in_time ?: '—') ?>">
                            <?= Html::encode(($visit->visitor?->full_name ?? 'Unknown visitor') . ' - ' . ($visit->host?->username ?? 'Unassigned')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($visits === []): ?><p class="visitor-checkout-empty">There are no active visitors to check out today.</p><?php endif; ?>

                <div id="visitor-details" class="visitor-details" hidden>
                    <span class="visitor-checkout-eyebrow">Selected visit</span>
                    <h2 id="visitor-name">Visitor</h2>
                    <dl>
                        <div><dt>Host</dt><dd id="visitor-host">—</dd></div>
                        <div><dt>Check-in time</dt><dd id="visitor-check-in">—</dd></div>
                    </dl>
                </div>

                <form id="checkout-form" method="post" action="<?= Html::encode(Yii::$app->urlManager->createUrl(['/visitor/do-checkout'])) ?>">
                    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->getCsrfToken()) ?>
                    <input type="hidden" name="id" id="checkout-id" value="">
                    <button type="submit" id="checkout-button" class="btn visitor-checkout-button" disabled>Check Out</button>
                </form>
                <p class="visitor-checkout-note">Please confirm only your own visit.</p>
            </div>
        </div>
    </div>
</div>
<?php $this->registerCss(<<<'CSS'
.visitor-checkout-page { background: linear-gradient(135deg, #f4f7f8 0%, #eef3f1 100%); margin: -1.5rem calc(50% - 50vw) -2.5rem; min-height: calc(100vh - 9rem); padding: 3rem 1rem; }
.visitor-checkout-shell { margin: 0 auto; max-width: 680px; }.visitor-checkout-card { background: #fff; border: 1px solid #e1e9e5; border-radius: 14px; box-shadow: 0 18px 45px rgba(23,48,39,.1); overflow: hidden; }.visitor-checkout-heading { align-items: flex-start; background: #183b31; color: #fff; display: flex; gap: 1rem; padding: 2rem 2.25rem; }.visitor-checkout-mark { align-items: center; background: #b9d8c7; color: #183b31; display: inline-flex; flex: 0 0 2.8rem; font-size: .68rem; font-weight: 900; height: 2.8rem; justify-content: center; }.visitor-checkout-eyebrow { color: #b9d8c7; display: block; font-size: .68rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }.visitor-checkout-heading h1 { font-size: 1.65rem; font-weight: 800; margin: .3rem 0 .35rem; }.visitor-checkout-heading p { color: #d8e8e0; margin: 0; }.visitor-checkout-body { padding: 2rem 2.25rem 2.25rem; }.visitor-checkout-body .form-label { color: #33443d; font-size: .82rem; font-weight: 700; }.visitor-search, .visitor-select { border-color: #d7e1dc; border-radius: 8px; padding: .7rem .8rem; }.visitor-search:focus, .visitor-select:focus { border-color: #4f9b72; box-shadow: 0 0 0 .2rem rgba(79,155,114,.16); }.visitor-select { min-height: 9rem; }.visitor-select option { padding: .55rem; }.visitor-details { background: #f4faf6; border: 1px solid #cfe5d7; border-radius: 9px; margin: 1.25rem 0; padding: 1.1rem 1.2rem; }.visitor-details h2 { font-size: 1.1rem; font-weight: 800; margin: .3rem 0 .8rem; }.visitor-details dl { margin: 0; }.visitor-details dl > div { display: flex; justify-content: space-between; gap: 1rem; padding: .35rem 0; }.visitor-details dt { color: #6d7c74; font-size: .78rem; font-weight: 700; }.visitor-details dd { font-size: .82rem; margin: 0; text-align: right; }.visitor-checkout-button { background: #d9534f; border: 0; border-radius: 8px; color: #fff; font-weight: 800; margin-top: 1.25rem; padding: .75rem 1rem; width: 100%; }.visitor-checkout-button:hover:not(:disabled) { background: #bd3f3b; color: #fff; }.visitor-checkout-button:disabled { cursor: not-allowed; opacity: .5; }.visitor-checkout-note, .visitor-checkout-empty { color: #74837b; font-size: .78rem; margin: .75rem 0 0; text-align: center; }
@media (max-width: 575.98px) { .visitor-checkout-page { margin-left: -.75rem; margin-right: -.75rem; padding: 1.25rem .75rem 2rem; }.visitor-checkout-heading { padding: 1.5rem; }.visitor-checkout-body { padding: 1.5rem; } }
CSS);
$this->registerJs(<<<'JS'
(function () {
    const search = document.getElementById('visitor-search');
    const select = document.getElementById('visitor-select');
    const details = document.getElementById('visitor-details');
    const checkoutId = document.getElementById('checkout-id');
    const checkoutButton = document.getElementById('checkout-button');
    const checkoutForm = document.getElementById('checkout-form');
    if (!search || !select || !details || !checkoutId || !checkoutButton || !checkoutForm) return;

    function updateDetails() {
        const option = select.options[select.selectedIndex];
        const selected = option && option.value;
        details.hidden = !selected;
        checkoutButton.disabled = !selected;
        checkoutId.value = selected || '';
        if (selected) {
            document.getElementById('visitor-name').textContent = option.dataset.name || 'Visitor';
            document.getElementById('visitor-host').textContent = option.dataset.host || 'Unassigned';
            document.getElementById('visitor-check-in').textContent = option.dataset.checkIn || '—';
        }
    }
    search.addEventListener('input', function () {
    const filter = search.value.toLowerCase().trim();
    Array.from(select.options).forEach(function (option, index) {
            if (index === 0) return;
            option.hidden = filter !== '' && !option.text.toLowerCase().includes(filter);
    });
    if (select.selectedOptions[0]?.hidden) {
            select.value = '';
            updateDetails();
    }
    });
    select.addEventListener('change', updateDetails);
    checkoutForm.addEventListener('submit', function (event) {
    if (!checkoutId.value || !window.confirm('Are you sure you want to check out?')) {
            event.preventDefault();
            return;
    }
    checkoutButton.disabled = true;
    checkoutButton.textContent = 'Processing...';
    });
}());
JS);
?>

<?php
declare(strict_types=1);

/** @var yii\web\View $this */
/** @var common\models\Visit|null $visit */
/** @var frontend\models\CheckOutForm $model */
/** @var string $step */

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$searchUrl = Yii::$app->urlManager->createUrl(['/visitor/search-active-visitors']);

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
                    <p>Search for the active visitor, confirm details, and complete checkout securely.</p>
                </div>
            </div>

            <div class="visitor-checkout-body">
                <?php if ($step === 'success' && $visit !== null): ?>
                    <div class="visitor-success-panel">
                        <div class="visitor-success-icon">✓</div>
                        <h2>Check-out successful</h2>
                        <p><?= Html::encode($visit->visitor?->full_name ?? 'Visitor') ?> has been checked out.</p>
                        <div class="visitor-summary-box">
                            <div><span>Name</span><strong><?= Html::encode($visit->visitor?->full_name ?? 'Unknown visitor') ?></strong></div>
                            <div><span>Check-in time</span><strong><?= Html::encode($visit->check_in_time ?: '—') ?></strong></div>
                        </div>
                        <div class="visitor-action-row">
                            <button type="button" class="btn visitor-primary-button" onclick="window.print()">Print receipt</button>
                            <a href="<?= Html::encode(Yii::$app->urlManager->createUrl(['/visitor/checkout-page'])) ?>" class="btn visitor-secondary-button">New search</a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php $form = ActiveForm::begin(['id' => 'visitor-checkout-form', 'method' => 'post', 'action' => ['/visitor/checkout-page'], 'options' => ['class' => 'visitor-checkout-form']]); ?>
                        <?= Html::hiddenInput('checkout_step', $step === 'preview' ? 'confirm' : 'preview', ['id' => 'checkout-step']) ?>
                        <?= Html::hiddenInput('visit_id', $visit?->id ?? '', ['id' => 'checkout-visit-id']) ?>
                        <?= $form->field($model, 'search', ['inputOptions' => ['id' => 'visitor-search', 'autocomplete' => 'off', 'placeholder' => 'Type visitor name']]) ?>
                        <div id="typed-visitor-name" class="visitor-typed-name" aria-live="polite"></div>

                        <?php if ($step === 'preview' && $visit !== null): ?>
                            <div class="visitor-details-card">
                                <span class="visitor-checkout-eyebrow">Selected visitor</span>
                                <h2><?= Html::encode($visit->visitor?->full_name ?? 'Unknown visitor') ?></h2>
                                <div class="visitor-detail-grid">
                                    <div><span>Phone</span><strong><?= Html::encode($visit->visitor?->phone_number ?? '—') ?></strong></div>
                                    <div><span>Check-in time</span><strong><?= Html::encode($visit->check_in_time ?: '—') ?></strong></div>
                                    <div><span>Pass</span><strong><?= Html::encode($visit->visitor_pass_number ?? '—') ?></strong></div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="visitor-action-row">
                            <?php if ($step === 'preview' && $visit !== null): ?>
                                <button type="submit" class="btn visitor-primary-button" id="confirm-checkout">Confirm checkout</button>
                                <a href="<?= Html::encode(Yii::$app->urlManager->createUrl(['/visitor/checkout-page'])) ?>" class="btn visitor-secondary-button">Cancel</a>
                            <?php else: ?>
                                <button type="submit" class="btn visitor-primary-button" id="continue-checkout">Continue</button>
                            <?php endif; ?>
                        </div>
                    <?php ActiveForm::end(); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $this->registerCss(<<<'CSS'
.visitor-checkout-page { background: linear-gradient(135deg, #0f172a 0%, #1a4c63 50%, #1aa39b 100%); margin: -1.5rem calc(50% - 50vw) -2.5rem; min-height: calc(100vh - 9rem); padding: 3rem 1rem; }
.visitor-checkout-shell { margin: 0 auto; max-width: 640px; }
.visitor-checkout-card { background: rgba(255,255,255,0.96); border: 1px solid rgba(15, 23, 42, 0.08); border-radius: 20px; box-shadow: 0 28px 60px rgba(15,23,42,0.18); overflow: hidden; }
.visitor-checkout-heading { align-items: flex-start; background: linear-gradient(135deg, #0f172a 0%, #1b2a3a 100%); color: #fff; display: flex; gap: 1rem; padding: 2rem 2.1rem 1.65rem; }
.visitor-checkout-mark { align-items: center; background: #dfeaf7; border-radius: 12px; color: #0f172a; display: inline-flex; flex: 0 0 3rem; font-size: .72rem; font-weight: 900; height: 3rem; justify-content: center; letter-spacing: .12em; }
.visitor-checkout-eyebrow { color: #8ec7d7; display: block; font-size: .68rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
.visitor-checkout-heading h1 { font-size: 1.8rem; font-weight: 800; margin: .35rem 0; }
.visitor-checkout-heading p { color: rgba(255,255,255,0.8); margin: 0; }
.visitor-checkout-body { padding: 2rem 2.1rem 2.2rem; }
.visitor-checkout-form { display: flex; flex-direction: column; gap: 1rem; }
.visitor-checkout-form .form-label { color: #1e2d3d; font-size: .8rem; font-weight: 700; }
.visitor-checkout-form .form-control { border: 1px solid #d5dde8; border-radius: 10px; min-height: 48px; padding: .8rem 1rem; }
.visitor-checkout-form .form-control:focus { border-color: #1a4c63; box-shadow: 0 0 0 .2rem rgba(26,76,99,.12); }
.visitor-typed-name { background: #f5fafc; border: 1px solid #dfeaf2; border-radius: 10px; color: #1e2d3d; display: none; margin-top: -.25rem; padding: .75rem .9rem; }
.visitor-typed-name.is-visible { display: block; }
.visitor-typed-name span { color: #64748b; display: block; font-size: .72rem; font-weight: 700; letter-spacing: .05em; margin-bottom: .2rem; text-transform: uppercase; }
.visitor-typed-name strong { font-size: 1rem; }
.visitor-search-results { display: none; background: #fff; border: 1px solid #dfe8ef; border-radius: 12px; box-shadow: 0 12px 24px rgba(15,23,42,.08); margin-top: -0.5rem; overflow: hidden; }
.visitor-search-results.is-visible { display: block; }
.visitor-search-item { background: #fff; border: 0; border-bottom: 1px solid #edf1f5; color: #1a2735; cursor: pointer; display: block; padding: .85rem 1rem; text-align: left; width: 100%; }
.visitor-search-item:last-child { border-bottom: 0; }
.visitor-search-item:hover, .visitor-search-item:focus { background: #f4f9fd; }
.visitor-search-item small { color: #5f7183; display: block; margin-top: .15rem; }
.visitor-details-card, .visitor-success-panel, .visitor-summary-box { background: #f5fafc; border: 1px solid #dfeaf2; border-radius: 12px; }
.visitor-details-card { padding: 1.15rem 1.1rem; }
.visitor-details-card h2 { font-size: 1.2rem; font-weight: 800; margin: .35rem 0 .8rem; }
.visitor-detail-grid { display: grid; gap: .75rem; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); }
.visitor-detail-grid div { background: #fff; border-radius: 10px; padding: .7rem .8rem; }
.visitor-detail-grid span { color: #64748b; display: block; font-size: .72rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
.visitor-detail-grid strong { font-size: .92rem; }
.visitor-action-row { display: flex; flex-wrap: wrap; gap: .75rem; margin-top: .25rem; }
.visitor-primary-button, .visitor-secondary-button { border-radius: 10px; font-weight: 700; padding: .8rem 1.25rem; }
.visitor-primary-button { background: linear-gradient(135deg, #1a4c63 0%, #0f172a 100%); border: 0; color: #fff; }
.visitor-secondary-button { background: #edf4f8; border: 1px solid #dfe9f0; color: #1e2d3d; }
.visitor-success-panel { padding: 1.5rem; text-align: center; }
.visitor-success-icon { align-items: center; background: #dff6ec; border-radius: 999px; color: #0f766e; display: inline-flex; font-size: 1.6rem; font-weight: 800; height: 64px; justify-content: center; margin-bottom: .8rem; width: 64px; }
.visitor-success-panel h2 { font-size: 1.35rem; font-weight: 800; margin: 0 0 .45rem; }
.visitor-success-panel p { color: #415466; margin: 0 0 1rem; }
.visitor-summary-box { margin-bottom: 1rem; padding: .9rem 1rem; text-align: left; }
.visitor-summary-box > div { align-items: center; display: flex; justify-content: space-between; gap: 1rem; padding: .5rem 0; }
.visitor-summary-box > div + div { border-top: 1px solid #e5edf4; }
.visitor-summary-box span { color: #64748b; font-size: .75rem; font-weight: 700; text-transform: uppercase; }
.visitor-summary-box strong { font-size: .96rem; }
@media (max-width: 575.98px) { .visitor-checkout-page { margin-left: -.75rem; margin-right: -.75rem; padding: 1.25rem .75rem 2rem; } .visitor-checkout-heading { padding: 1.2rem 1.25rem; } .visitor-checkout-body { padding: 1.25rem; } .visitor-action-row { flex-direction: column; } .visitor-action-row .btn { width: 100%; } }
CSS);

$this->registerJs(
    '(function () {'
    . 'const searchInput = document.getElementById("visitor-search");'
    . 'const typedNameBox = document.getElementById("typed-visitor-name");'
    . 'const form = document.getElementById("visitor-checkout-form");'
    . 'const checkoutStep = document.getElementById("checkout-step");'
    . 'const checkoutVisitId = document.getElementById("checkout-visit-id");'
    . 'const searchUrl = ' . json_encode($searchUrl, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';'
    . 'if (!searchInput || !form || !checkoutStep || !checkoutVisitId) { return; }'
    . 'let selectedVisitorId = checkoutVisitId.value || "";'
    . 'function updateWrittenName(name) {'
    . 'if (!typedNameBox) { return; }'
    . 'const value = (name || "").trim();'
    . 'if (!value) { typedNameBox.classList.remove("is-visible"); typedNameBox.innerHTML = ""; return; }'
    . 'typedNameBox.classList.add("is-visible");'
    . 'typedNameBox.innerHTML = "<span>Written name</span><strong>" + value + "</strong>";'
    . '}'
    . 'function normalizeName(value) {'
    . 'return (value || "").toLowerCase().replace(/\\s+/g, " ").trim();'
    . '}'
    . 'function clearSelection() {'
    . 'selectedVisitorId = "";'
    . 'checkoutVisitId.value = "";'
    . 'const detailCard = document.querySelector(".visitor-details-card");'
    . 'if (detailCard) { detailCard.remove(); }'
    . '}'
    . 'function showSelectedVisitor(item) {'
    . 'selectedVisitorId = String(item.id);'
    . 'checkoutVisitId.value = selectedVisitorId;'
    . 'searchInput.value = item.full_name || searchInput.value;'
    . 'updateWrittenName(item.full_name || searchInput.value);'
    . 'const detailCard = document.querySelector(".visitor-details-card");'
    . 'if (!detailCard) {'
    . 'const wrapper = document.createElement("div");'
    . 'wrapper.className = "visitor-details-card";'
    . 'wrapper.innerHTML = "<span class=\"visitor-checkout-eyebrow\">Selected visitor</span><h2>" + (item.full_name || "Unknown visitor") + "</h2><div class=\"visitor-detail-grid\"><div><span>Phone</span><strong>" + (item.phone_number || "—") + "</strong></div><div><span>Check-in time</span><strong>" + (item.check_in_time || "—") + "</strong></div><div><span>Pass</span><strong>" + (item.visitor_pass_number || "—") + "</strong></div></div>";'
    . 'form.insertBefore(wrapper, form.querySelector(".visitor-action-row"));'
    . '} else {'
    . 'detailCard.innerHTML = "<span class=\"visitor-checkout-eyebrow\">Selected visitor</span><h2>" + (item.full_name || "Unknown visitor") + "</h2><div class=\"visitor-detail-grid\"><div><span>Phone</span><strong>" + (item.phone_number || "—") + "</strong></div><div><span>Check-in time</span><strong>" + (item.check_in_time || "—") + "</strong></div><div><span>Pass</span><strong>" + (item.visitor_pass_number || "—") + "</strong></div></div>";'
    . '}'
    . '}'
    . 'function searchExactMatch(query, callback) {'
    . 'const normalizedQuery = normalizeName(query);'
    . 'if (!normalizedQuery || normalizedQuery.length < 2) { callback(null); return; }'
    . 'fetch(searchUrl + "?q=" + encodeURIComponent(query), { headers: { "X-Requested-With": "XMLHttpRequest" } })'
    . '.then(function (response) { return response.json(); })'
    . '.then(function (items) {'
    . 'if (!Array.isArray(items) || !items.length) { callback(null); return; }'
    . 'const exactMatch = items.find(function (item) { return normalizeName(item.full_name) === normalizedQuery; });'
    . 'callback(exactMatch || null);'
    . '})'
    . '.catch(function () { callback(null); });'
    . '}'
    . 'searchInput.addEventListener("input", function () {'
    . 'const query = searchInput.value.trim();'
    . 'updateWrittenName(query);'
    . 'if (!query) { clearSelection(); return; }'
    . 'searchExactMatch(query, function (match) {'
    . 'if (match) { showSelectedVisitor(match); } else { clearSelection(); }'
    . '});'
    . '});'
    . 'form.addEventListener("submit", function (event) {'
    . 'const query = searchInput.value.trim();'
    . 'if (!query) {'
    . 'event.preventDefault();'
    . 'searchInput.focus();'
    . 'return;'
    . '}'
    . 'if (!selectedVisitorId) {'
    . 'event.preventDefault();'
    . 'alert("Please type the exact checked-in visitor name to continue.");'
    . 'return;'
    . '}'
    . 'checkoutVisitId.value = selectedVisitorId;'
    . 'checkoutStep.value = checkoutStep.value === "preview" ? "confirm" : "preview";'
    . '});'
    . '})();'
);
?>

<?php

declare(strict_types=1);

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Live Analytics';
$this->params['breadcrumbs'][] = $this->title;
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js', ['position' => yii\web\View::POS_HEAD]);
?>
<div class="mb-3 d-flex flex-wrap gap-2">
    <?= Html::a('← Back', ['/admin/reports'], ['class' => 'btn btn-light border']) ?>
    <?= Html::a('🏠 Dashboard', ['/admin/dashboard'], ['class' => 'btn btn-warning']) ?>
</div>
<h1 class="h3 mb-4">Analytics Dashboard</h1>
<div class="row g-4">
    <div class="col-lg-6"><div class="card shadow-sm h-100"><div class="card-header bg-transparent"><h2 class="h5 mb-0">Visitors by branch</h2></div><div class="card-body"><canvas id="branch-chart"></canvas></div></div></div>
    <div class="col-lg-6"><div class="card shadow-sm h-100"><div class="card-header bg-transparent"><h2 class="h5 mb-0">Visitors by department</h2></div><div class="card-body"><canvas id="department-chart"></canvas></div></div></div>
    <div class="col-lg-5"><div class="card shadow-sm h-100"><div class="card-header bg-transparent"><h2 class="h5 mb-0">Most common gender</h2></div><div class="card-body"><canvas id="gender-chart"></canvas></div></div></div>
    <div class="col-lg-7"><div class="card shadow-sm h-100"><div class="card-header bg-transparent"><h2 class="h5 mb-0">Common visitors</h2><p class="small text-body-secondary mb-0">Visitors with the highest number of visits</p></div><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Visitor</th><th>Phone</th><th>Visits</th></tr></thead><tbody id="common-visitors-body"><tr><td colspan="3" class="text-center text-body-secondary py-4">Loading...</td></tr></tbody></table></div></div></div>
</div>
<?php $chartDataUrl = Url::to(['/dashboard/chart-data']); ?>
<?php $this->registerJs(<<<JS
fetch('{$chartDataUrl}', { headers: { 'Accept': 'application/json' } }).then(function (response) {
    if (!response.ok) throw new Error('Analytics request failed with status ' + response.status);
    return response.json();
}).then(function (response) {
    if (!response.success || typeof Chart === 'undefined') return;
    const data = response.data;
    const chart = function (id, rows, key, type) { const canvas = document.getElementById(id); if (!canvas) return; new Chart(canvas, { type: type, data: { labels: rows.map(function (row) { return row[key]; }), datasets: [{ data: rows.map(function (row) { return row.count; }), backgroundColor: ['#0d6efd', '#198754', '#f0ad4e', '#dc3545', '#6f42c1', '#20c997', '#fd7e14'] }] }, options: { responsive: true, plugins: { legend: { position: type === 'doughnut' ? 'bottom' : 'top' } }, scales: type === 'bar' ? { y: { beginAtZero: true, ticks: { precision: 0 } } } : {} } }); };
    chart('branch-chart', data.branchVisits, 'branch', 'bar');
    chart('department-chart', data.departmentVisits, 'department', 'bar');
    chart('gender-chart', data.genderVisits, 'gender', 'doughnut');
    const body = document.getElementById('common-visitors-body');
    if (body) {
        body.replaceChildren();
        if (!data.commonVisitors.length) {
            const emptyRow = document.createElement('tr');
            const emptyCell = document.createElement('td');
            emptyCell.colSpan = 3;
            emptyCell.className = 'text-center text-body-secondary py-4';
            emptyCell.textContent = 'No visitor records found.';
            emptyRow.appendChild(emptyCell);
            body.appendChild(emptyRow);
        } else {
            data.commonVisitors.forEach(function (visitor) {
                const row = document.createElement('tr');
                const name = document.createElement('td');
                name.className = 'fw-semibold';
                name.textContent = visitor.name || 'Unknown visitor';
                const phone = document.createElement('td');
                phone.textContent = visitor.phone || '—';
                const count = document.createElement('td');
                const badge = document.createElement('span');
                badge.className = 'badge text-bg-primary';
                badge.textContent = visitor.count;
                count.appendChild(badge);
                row.append(name, phone, count);
                body.appendChild(row);
            });
        }
    }
}).catch(function (error) {
    console.error('Analytics failed to load:', error);
});
JS); ?>

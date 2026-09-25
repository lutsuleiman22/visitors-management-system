<?php

declare(strict_types=1);

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Live Analytics';
$this->params['breadcrumbs'][] = $this->title;
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js', ['position' => yii\web\View::POS_HEAD]);
?>
<div class="dashboard-analytics-watermark">
<div class="mb-3 d-flex flex-wrap gap-2">
    <?= Html::a('← Back', ['/admin/reports'], ['class' => 'btn btn-light border']) ?>
    <?= Html::a('🏠 Dashboard', ['/admin/dashboard'], ['class' => 'btn btn-warning']) ?>
</div>
 </div>
<?php $this->registerCss(<<<'CSS'
.dashboard-analytics-watermark { position: relative; isolation: isolate; }
.dashboard-analytics-watermark::before { background: url('/visitors-management-system/frontend/web/images/pbz%20images.png') center / min(42vw, 480px) no-repeat; content: ''; inset: 0; opacity: .08; pointer-events: none; position: absolute; z-index: 0; }.dashboard-analytics-watermark > * { position: relative; z-index: 1; }
CSS); ?>
<h1 class="h3 mb-4">Analytics Dashboard</h1>
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card shadow-sm h-100 border-0" style="background:#fff; border-radius:14px;">
            <div class="card-header bg-transparent border-0 px-3 pt-3 pb-0">
                <h2 class="h5 mb-0 fw-semibold">Visitors by branch</h2>
            </div>
            <div class="card-body p-3 pt-2"><canvas id="branch-chart"></canvas></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm h-100 border-0" style="background:#fff; border-radius:14px;">
            <div class="card-header bg-transparent border-0 px-3 pt-3 pb-0">
                <h2 class="h5 mb-0 fw-semibold">Visitors by department</h2>
            </div>
            <div class="card-body p-3 pt-2"><canvas id="department-chart"></canvas></div>
        </div>
    </div>
    <div class="col-lg-5"><div class="card shadow-sm h-100"><div class="card-header bg-transparent"><h2 class="h5 mb-0">Most common gender</h2></div><div class="card-body"><canvas id="gender-chart"></canvas></div></div></div>
    <div class="col-lg-7"><div class="card shadow-sm h-100"><div class="card-header bg-transparent"><h2 class="h5 mb-0">Common visitors</h2><p class="small text-body-secondary mb-0">Visitors with the highest number of visits</p></div><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Visitor</th><th>Phone</th><th>Visits</th></tr></thead><tbody id="common-visitors-body"><tr><td colspan="3" class="text-center text-body-secondary py-4">Loading...</td></tr></tbody></table></div></div></div>
</div>
<?php $chartDataUrl = Url::to(['/dashboard/chart-data']); ?>
<?php $this->registerCss(<<<'CSS'
.dashboard-analytics-watermark { position: relative; isolation: isolate; }
.dashboard-analytics-watermark::before { background: url('/visitors-management-system/frontend/web/images/pbz%20images.png') center / min(42vw, 480px) no-repeat; content: ''; inset: 0; opacity: .08; pointer-events: none; position: absolute; z-index: 0; }
.dashboard-analytics-watermark > * { position: relative; z-index: 1; }
CSS); ?>
<?php $this->registerJs(<<<JS
(function () {
    if (typeof Chart === 'undefined') {
        return;
    }

    const chartRegistry = window.__analyticsCharts || (window.__analyticsCharts = {});

    const normalizeRows = function (rows) {
        return Array.isArray(rows) ? rows : [];
    };

    const palette = ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#14b8a6', '#f97316', '#3b82f6'];

    const renderChart = function (id, rows, key, type) {
        const canvas = document.getElementById(id);
        if (!canvas) {
            return;
        }

        if (chartRegistry[id]) {
            chartRegistry[id].destroy();
        }

        const safeRows = normalizeRows(rows);
        const labels = safeRows.length ? safeRows.map(function (row) {
            return row && row[key] !== undefined && row[key] !== null ? row[key] : 'Unknown';
        }) : ['No data'];
        const values = safeRows.length ? safeRows.map(function (row) {
            return Number(row && row.count !== undefined ? row.count : 0) || 0;
        }) : [0];

        chartRegistry[id] = new Chart(canvas, {
            type: type,
            data: {
                labels: labels,
                datasets: [{
                    label: 'Visitors',
                    data: values,
                    backgroundColor: palette.slice(0, Math.max(labels.length, 1)),
                    borderColor: '#ffffff',
                    borderWidth: 0,
                    borderRadius: type === 'bar' ? 4 : 0,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 250 },
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        enabled: true,
                        callbacks: {
                            label: function (context) {
                                return 'Visitors: ' + context.parsed.y;
                            }
                        }
                    }
                },
                scales: type === 'bar' ? {
                    x: {
                        grid: { display: false },
                        ticks: { maxRotation: 32, minRotation: 0, font: { size: 11 } }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0, stepSize: 1 },
                        grid: { color: 'rgba(148,163,184,0.12)' }
                    }
                } : {
                    x: { display: false },
                    y: { display: false }
                }
            }
        });
    };

    fetch('{$chartDataUrl}', { headers: { 'Accept': 'application/json' } })
        .then(function (response) {
            if (!response.ok) throw new Error('Analytics request failed with status ' + response.status);
            return response.json();
        })
        .then(function (response) {
            if (!response || !response.success || !response.data || typeof Chart === 'undefined') {
                return;
            }

            const data = response.data;
            renderChart('branch-chart', data.branchVisits, 'branch', 'bar');
            renderChart('department-chart', data.departmentVisits, 'department', 'bar');
            renderChart('gender-chart', data.genderVisits, 'gender', 'doughnut');

            const body = document.getElementById('common-visitors-body');
            if (!body) return;

            body.replaceChildren();
            const visitors = Array.isArray(data.commonVisitors) ? data.commonVisitors : [];

            if (!visitors.length) {
                const emptyRow = document.createElement('tr');
                const emptyCell = document.createElement('td');
                emptyCell.colSpan = 3;
                emptyCell.className = 'text-center text-body-secondary py-4';
                emptyCell.textContent = 'No visitor records found.';
                emptyRow.appendChild(emptyCell);
                body.appendChild(emptyRow);
                return;
            }

            visitors.forEach(function (visitor) {
                const row = document.createElement('tr');
                const name = document.createElement('td');
                name.className = 'fw-semibold';
                name.textContent = visitor && visitor.name ? visitor.name : 'Unknown visitor';

                const phone = document.createElement('td');
                phone.textContent = visitor && visitor.phone ? visitor.phone : '—';

                const count = document.createElement('td');
                const badge = document.createElement('span');
                badge.className = 'badge text-bg-primary';
                badge.textContent = visitor && visitor.count ? visitor.count : 0;
                count.appendChild(badge);

                row.append(name, phone, count);
                body.appendChild(row);
            });
        })
        .catch(function (error) {
            console.error('Analytics failed to load:', error);
        });
})();
JS); ?>

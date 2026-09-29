<?php

declare(strict_types=1);

$basePath = '../../';
require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/AssetService.php';

use App\Service\AssetService;

$assetService = new AssetService($pdo);
$assetAccessKeywords = ['assets', 'asset', 'asset inventory', 'asset inventory tracker'];

if (!$isSuperAdmin && !$hasResourceAccess($assetAccessKeywords)) {
    header('Location: ' . $basePath . 'pages/dashboard.php?error=access_denied');
    exit;
}

$assets = $assetService->getAssets();
$totalAssets = count($assets);
$activeAssets = count(array_filter($assets, static fn(array $asset): bool => ($asset['lifecycle_status'] ?? '') === 'active'));
$maintenanceDue = count($assetService->getAssets(['maintenance_due' => true]));
$categories = $assetService->getCategories();

// Aggregate stats for the Status Breakdown panel
$stats = $assetService->getStats();

// Data for the maintenance line chart + CSV export
$maintenanceStats = [
    'today' => $assetService->getMaintenanceStats('today'),
    'week'  => $assetService->getMaintenanceStats('week'),
    'month' => $assetService->getMaintenanceStats('month'),
    'year'  => $assetService->getMaintenanceStats('year'),
];

// Condition rows for the donut chart + legend
$conditionRows = [
    ['label' => 'Good',         'key' => 'good',         'color' => '#10b981'],
    ['label' => 'Fair',         'key' => 'fair',         'color' => '#3b82f6'],
    ['label' => 'Poor',         'key' => 'poor',         'color' => '#f59e0b'],
    ['label' => 'Damaged',      'key' => 'damaged',      'color' => '#f43f5e'],
    ['label' => 'Under Repair', 'key' => 'under_repair', 'color' => '#6366f1'],
];
$lifecycleRows = [
    ['label' => 'Active',   'key' => 'active',   'color' => '#10b981'],
    ['label' => 'Retired',  'key' => 'retired',  'color' => '#f59e0b'],
    ['label' => 'Disposed', 'key' => 'disposed', 'color' => '#64748b'],
];

$pageTitle = 'Asset Inventory Dashboard';
include $basePath . 'includes/header.php';
include $basePath . 'includes/sidebar.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<div class="flex-1 p-6 lg:p-8">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">
                <i class="fa-solid fa-boxes-stacked text-brand-medium mr-3"></i>Asset Inventory Dashboard
            </h1>
            <div class="flex gap-3">
                <a href="<?= $basePath ?>pages/assets/add.php" class="text-sm font-bold bg-brand-dark hover:bg-[#0e4f62] text-white px-4 py-2 rounded-lg transition">
                    <i class="fa-solid fa-plus mr-1"></i> Add Asset
                </a>
                <a href="<?= $basePath ?>pages/assets/category.php" class="text-sm font-bold text-brand-medium hover:text-brand-dark transition">
                    <i class="fa-solid fa-tags mr-1"></i> Categories
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Assets</p>
                <p class="text-2xl font-black text-slate-800 dark:text-white mt-1"><?= $totalAssets ?></p>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Active Assets</p>
                <p class="text-2xl font-black text-emerald-600 mt-1"><?= $activeAssets ?></p>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Maintenance Due</p>
                <p class="text-2xl font-black text-amber-600 mt-1"><?= $maintenanceDue ?></p>
            </div>
        </div>

        <!-- Chart + Status Breakdown Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

            <!-- Maintenance Activity Chart (spans 2 columns) -->
            <div class="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm flex flex-col">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="font-bold text-slate-800 dark:text-white">
                            <i class="fa-solid fa-chart-line text-brand-medium mr-2"></i>Maintenance Activity
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5" id="chart-subtitle">
                            Showing: This Week
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="inline-flex rounded-lg border border-slate-200 dark:border-slate-700 overflow-hidden" role="group">
                            <button type="button" data-period="today" class="period-btn px-3 py-1.5 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">Today</button>
                            <button type="button" data-period="week"  class="period-btn px-3 py-1.5 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition border-l border-r border-slate-200 dark:border-slate-700">Week</button>
                            <button type="button" data-period="month" class="period-btn px-3 py-1.5 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition border-r border-slate-200 dark:border-slate-700">Month</button>
                            <button type="button" data-period="year"  class="period-btn px-3 py-1.5 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">Year</button>
                        </div>
                        <button type="button" id="exportChart"
                                class="text-xs font-bold bg-brand-dark hover:bg-[#0e4f62] text-white px-3 py-1.5 rounded-lg transition shadow-sm">
                            <i class="fa-solid fa-file-csv mr-1"></i> Export CSV (All Periods)
                        </button>
                    </div>
                </div>
                <div class="p-6 flex-1 flex flex-col">
                    <div style="position: relative; height: 320px;" class="flex-1">
                        <canvas id="maintenanceChart"></canvas>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-3">
                        <i class="fa-solid fa-circle-info mr-1"></i>
                        Line shows number of maintenance events (left axis). Dashed line shows total cost in ₱ (right axis).
                    </p>
                </div>
            </div>

            <!-- Status Breakdown Panel (1 column) -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm flex flex-col">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800">
                    <h2 class="font-bold text-slate-800 dark:text-white">
                        <i class="fa-solid fa-chart-pie text-brand-medium mr-2"></i>Status Breakdown
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">By condition &amp; lifecycle</p>
                </div>

                <div class="p-6 flex-1 flex flex-col">
                    <!-- Condition donut -->
                    <div style="position: relative; height: 190px;">
                        <canvas id="conditionChart"></canvas>
                    </div>

                    <!-- Condition legend -->
                    <div class="mt-4 space-y-1.5">
                        <?php foreach ($conditionRows as $row):
                            $val = (int)($stats[$row['key']] ?? 0);
                            $pct = $totalAssets > 0 ? round(($val / $totalAssets) * 100, 1) : 0.0;
                        ?>
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background: <?= $row['color'] ?>"></span>
                                    <span class="text-slate-600 dark:text-slate-400 font-medium"><?= $row['label'] ?></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-800 dark:text-white"><?= $val ?></span>
                                    <span class="text-slate-400 text-[10px] w-10 text-right"><?= number_format($pct, 1) ?>%</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Lifecycle status bars -->
                    <div class="border-t border-slate-200 dark:border-slate-800 mt-4 pt-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">
                            <i class="fa-solid fa-arrows-spin mr-1"></i>Lifecycle Status
                        </p>
                        <?php foreach ($lifecycleRows as $row):
                            $val = (int)($stats[$row['key']] ?? 0);
                            $pct = $totalAssets > 0 ? ($val / $totalAssets) * 100 : 0.0;
                        ?>
                            <div class="mb-2.5 last:mb-0">
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-slate-600 dark:text-slate-400 font-medium"><?= $row['label'] ?></span>
                                    <span class="font-bold text-slate-800 dark:text-white"><?= $val ?></span>
                                </div>
                                <div class="w-full h-1.5 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                                    <div class="h-full rounded-full transition-all"
                                         style="width: <?= number_format($pct, 2) ?>%; background: <?= $row['color'] ?>"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Registered Assets Table -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <h2 class="font-bold text-slate-800 dark:text-white">Registered Assets</h2>
                <span class="text-xs font-bold text-slate-400"><?= count($categories) ?> categories</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 text-xs font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3 text-left">Asset</th>
                            <th class="px-6 py-3 text-left">Category</th>
                            <th class="px-6 py-3 text-left">Plate</th>
                            <th class="px-6 py-3 text-left">Location</th>
                            <th class="px-6 py-3 text-left">Condition</th>
                            <th class="px-6 py-3 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php foreach ($assets as $asset): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                <td class="px-6 py-3 font-bold"><a class="text-brand-medium hover:underline" href="<?= $basePath ?>pages/assets/views.php?id=<?= (int)$asset['id'] ?>"><?= htmlspecialchars((string)$asset['name']) ?></a></td>
                                <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= htmlspecialchars((string)($asset['category_name'] ?? 'Uncategorized')) ?></td>
                                <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= htmlspecialchars((string)($asset['plate_number'] ?? '')) ?></td>
                                <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= htmlspecialchars((string)$asset['location_text']) ?></td>
                                <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= htmlspecialchars((string)$asset['condition']) ?></td>
                                <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= htmlspecialchars((string)$asset['lifecycle_status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$assets): ?>
                            <tr><td colspan="6" class="px-6 py-8 text-center text-slate-400">No assets registered yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // ---- Data injected from PHP ----
    var MAINTENANCE_STATS = <?= json_encode($maintenanceStats, JSON_UNESCAPED_SLASHES) ?>;
    var TOTAL_ASSETS = <?= (int)$totalAssets ?>;
    var CONDITION_STATS = {
        labels: <?= json_encode(array_column($conditionRows, 'label')) ?>,
        values: <?= json_encode(array_map(fn($r) => (int)($stats[$r['key']] ?? 0), $conditionRows)) ?>,
        colors: <?= json_encode(array_column($conditionRows, 'color')) ?>
    };
    var PERIODS = ['today', 'week', 'month', 'year'];
    var PERIOD_LABELS = {
        today: 'Today (hourly)',
        week:  'This Week (last 7 days)',
        month: 'This Month (last 30 days)',
        year:  'This Year (last 12 months)'
    };

    var chart = null;
    var currentPeriod = 'week';

    function buildDatasets(data) {
        return [
            {
                label: 'Maintenance Events',
                data: data.counts,
                borderColor: '#0e7490',
                backgroundColor: 'rgba(14, 116, 144, 0.12)',
                borderWidth: 2,
                tension: 0.35,
                fill: true,
                pointRadius: 3,
                pointHoverRadius: 5,
                yAxisID: 'y'
            },
            {
                label: 'Total Cost (₱)',
                data: data.costs,
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245, 158, 11, 0.08)',
                borderWidth: 2,
                borderDash: [6, 4],
                tension: 0.35,
                fill: false,
                pointRadius: 3,
                pointHoverRadius: 5,
                yAxisID: 'y1'
            }
        ];
    }

    function renderChart(period) {
        currentPeriod = period;
        var data = MAINTENANCE_STATS[period] || MAINTENANCE_STATS.week;

        document.getElementById('chart-subtitle').textContent = 'Showing: ' + (PERIOD_LABELS[period] || period);

        document.querySelectorAll('.period-btn').forEach(function(btn) {
            var active = btn.getAttribute('data-period') === period;
            btn.classList.toggle('bg-brand-medium', active);
            btn.classList.toggle('text-white', active);
            btn.classList.toggle('text-slate-600', !active);
            btn.classList.toggle('dark:text-slate-300', !active);
        });

        if (chart) {
            chart.data.labels = data.labels;
            chart.data.datasets = buildDatasets(data);
            chart.update();
            return;
        }

        var ctx = document.getElementById('maintenanceChart').getContext('2d');
        chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: buildDatasets(data)
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            color: '#64748b',
                            font: { size: 11, weight: '600' }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                if (ctx.dataset.yAxisID === 'y1') {
                                    return ctx.dataset.label + ': ₱' + Number(ctx.parsed.y).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                }
                                return ctx.dataset.label + ': ' + ctx.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#94a3b8', font: { size: 10 } }
                    },
                    y: {
                        beginAtZero: true,
                        position: 'left',
                        title: { display: true, text: 'Events', color: '#0e7490', font: { size: 11, weight: '700' } },
                        ticks: { precision: 0, color: '#94a3b8', font: { size: 10 } },
                        grid: { color: 'rgba(148, 163, 184, 0.15)' }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        title: { display: true, text: 'Cost (₱)', color: '#f59e0b', font: { size: 11, weight: '700' } },
                        ticks: { color: '#94a3b8', font: { size: 10 } },
                        grid: { drawOnChartArea: false }
                    }
                }
            }
        });
    }

    // ---- Center-text plugin for the condition donut ----
    var centerTextPlugin = {
        id: 'centerText',
        afterDraw: function(c) {
            if (c.config.type !== 'doughnut') return;
            var meta = c.getDatasetMeta(0);
            if (!meta.data.length) return;
            var x = meta.data[0].x;
            var y = meta.data[0].y;
            var isDark = document.documentElement.classList.contains('dark');
            var ctx = c.ctx;
            ctx.save();
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.font = 'bold 22px ui-sans-serif, system-ui, sans-serif';
            ctx.fillStyle = isDark ? '#ffffff' : '#0f172a';
            ctx.fillText(TOTAL_ASSETS, x, y - 6);
            ctx.font = '600 9px ui-sans-serif, system-ui, sans-serif';
            ctx.fillStyle = '#94a3b8';
            ctx.fillText('TOTAL ASSETS', x, y + 13);
            ctx.restore();
        }
    };

    function renderConditionChart() {
        var ctx = document.getElementById('conditionChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: CONDITION_STATS.labels,
                datasets: [{
                    data: CONDITION_STATS.values,
                    backgroundColor: CONDITION_STATS.colors,
                    borderWidth: 0,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '66%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(c) {
                                var total = c.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                                var pct = total > 0 ? ((c.parsed / total) * 100).toFixed(1) : '0.0';
                                return c.label + ': ' + c.parsed + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            },
            plugins: [centerTextPlugin]
        });
    }

    // ---- CSV export helpers ----
    function csvCell(value) {
        var s = String(value === null || value === undefined ? '' : value);
        return '"' + s.replace(/"/g, '""') + '"';
    }

    function exportAllCsv() {
        var rows = [];

        rows.push(['Maintenance Activity Export']);
        rows.push(['Generated', new Date().toISOString()]);
        rows.push([]);
        rows.push(['Period', 'Total Events', 'Total Cost (PHP)']);
        PERIODS.forEach(function(p) {
            var d = MAINTENANCE_STATS[p] || {counts: [], costs: []};
            var totalEvents = (d.counts || []).reduce(function(a, b) { return a + Number(b); }, 0);
            var totalCost = (d.costs || []).reduce(function(a, b) { return a + Number(b); }, 0);
            rows.push([PERIOD_LABELS[p] || p, totalEvents, totalCost.toFixed(2)]);
        });
        rows.push([]);

        PERIODS.forEach(function(p) {
            var d = MAINTENANCE_STATS[p] || {labels: [], counts: [], costs: []};
            rows.push(['=== ' + (PERIOD_LABELS[p] || p) + ' ===']);
            rows.push(['Bucket', 'Maintenance Events', 'Total Cost (PHP)']);
            for (var i = 0; i < d.labels.length; i++) {
                rows.push([d.labels[i], d.counts[i] ?? 0, (d.costs[i] ?? 0).toFixed(2)]);
            }
            var tE = (d.counts || []).reduce(function(a, b) { return a + Number(b); }, 0);
            var tC = (d.costs || []).reduce(function(a, b) { return a + Number(b); }, 0);
            rows.push(['TOTAL', tE, tC.toFixed(2)]);
            rows.push([]);
        });

        var csv = rows.map(function(row) { return row.map(csvCell).join(','); }).join('\r\n');
        var blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        var stamp = new Date().toISOString().slice(0, 10);
        a.href = url;
        a.download = 'maintenance-all-periods-' + stamp + '.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    document.addEventListener('DOMContentLoaded', function() {
        renderChart('week');
        renderConditionChart();

        document.querySelectorAll('.period-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                renderChart(btn.getAttribute('data-period'));
            });
        });

        document.getElementById('exportChart').addEventListener('click', exportAllCsv);
    });
</script>

<?php include $basePath . 'includes/footer.php'; ?>
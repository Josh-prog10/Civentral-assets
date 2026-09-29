<?php
declare(strict_types=1);

$basePath = '../../';
require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/CemeteryService.php';

use App\Service\CemeteryService;

$cemeteryService = new CemeteryService($pdo);

if (!$isSuperAdmin && !$hasResourceAccess(['cemetery', 'burial', 'lot'])) {
    header('Location: ' . $basePath . 'pages/dashboard.php?error=access_denied');
    exit;
}

$summary = $cemeteryService->getAvailabilitySummary();
$recent = $cemeteryService->getRecentBurials(5);
$totalAvailable = $cemeteryService->getAvailableLotsCount();
$totalCemeteries = count($cemeteryService->getCemeteries());
$stats = $cemeteryService->getStatisticalSummary();

// -------- Status breakdown data --------
// Map each status -> count (default 0 if not present)
$statusCounts = ['available' => 0, 'reserved' => 0, 'occupied' => 0];
foreach ($summary as $row) {
    $statusCounts[strtolower($row['status'])] = (int)$row['count'];
}
$totalLots = array_sum($statusCounts);

$statusMeta = [
    'available' => ['label' => 'Available', 'bar' => 'bg-emerald-500', 'text' => 'text-emerald-700 dark:text-emerald-300', 'bg' => 'bg-emerald-100 dark:bg-emerald-900/30'],
    'reserved'  => ['label' => 'Reserved',  'bar' => 'bg-amber-500',   'text' => 'text-amber-700 dark:text-amber-300',   'bg' => 'bg-amber-100 dark:bg-amber-900/30'],
    'occupied'  => ['label' => 'Occupied',  'bar' => 'bg-rose-500',    'text' => 'text-rose-700 dark:text-rose-300',     'bg' => 'bg-rose-100 dark:bg-rose-900/30'],
];

// -------- CSV Export (server-side) --------
if (($_GET['export'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="cemetery_statistics_' . date('Y-m-d') . '.csv"');
    header('Cache-Control: no-store');

    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel

    // Section 1: Burial & Deceased stats
    fputcsv($out, ['Burial & Deceased Statistics']);
    fputcsv($out, ['Period', 'Burials', 'Deceased']);
    foreach (['today' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year'] as $key => $label) {
        fputcsv($out, [$label, $stats['burials'][$key], $stats['deceased'][$key]]);
    }

    // Section 2: Lot status breakdown
    fputcsv($out, []);
    fputcsv($out, ['Lot Status Breakdown']);
    fputcsv($out, ['Status', 'Count', 'Percentage']);
    foreach ($statusCounts as $status => $count) {
        $label = $statusMeta[$status]['label'] ?? ucfirst($status);
        $pct = $totalLots > 0 ? round(($count / $totalLots) * 100, 1) : 0;
        fputcsv($out, [$label, $count, $pct . '%']);
    }
    fputcsv($out, ['Total', $totalLots, '100%']);

    fclose($out);
    exit;
}

$pageTitle = 'Cemetery Dashboard';
include $basePath . 'includes/header.php';
include $basePath . 'includes/sidebar.php';
?>

<div class="flex-1 p-6 lg:p-8">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">
                <i class="fa-solid fa-cemetery text-brand-medium mr-3"></i>Cemetery & Burial Management
            </h1>
            <!-- NEW: Link to Cemeteries management -->
            <a href="<?= $basePath ?>pages/cemetery/cemeteries.php" 
               class="text-sm font-bold text-brand-medium hover:text-brand-dark dark:hover:text-brand-light transition">
                <i class="fa-solid fa-map-location-dot mr-1"></i> Manage Cemeteries
            </a>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
            <?php foreach ($summary as $row): ?>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500"><?= ucfirst($row['status']) ?></p>
                    <p class="text-2xl font-black text-slate-800 dark:text-white mt-1"><?= $row['count'] ?></p>
                </div>
            <?php endforeach; ?>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Available Lots</p>
                <p class="text-2xl font-black text-brand-dark dark:text-brand-medium mt-1"><?= $totalAvailable ?></p>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Cemeteries</p>
                <p class="text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-1"><?= $totalCemeteries ?></p>
            </div>
        </div>

        <!-- Statistical Graph + Status Breakdown -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm mb-8" id="statsCard">

            <!-- Header row: title + export buttons -->
            <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                <h2 class="font-bold text-slate-800 dark:text-white">
                    <i class="fa-solid fa-chart-line text-brand-medium mr-2"></i>Statistics & Breakdown
                </h2>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mr-1">
                        Today · Week · Month · Year
                    </span>

                    <!-- Export CSV -->
                    <a href="?export=csv"
                       class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold rounded-lg
                              bg-emerald-100 text-emerald-700 hover:bg-emerald-200
                              dark:bg-emerald-900/30 dark:text-emerald-300 dark:hover:bg-emerald-900/50
                              transition"
                       title="Download the numbers as a CSV file">
                        <i class="fa-solid fa-file-csv"></i> CSV
                    </a>

                    <!-- Export PNG (client-side) -->
                    <button type="button" id="exportPngBtn"
                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold rounded-lg
                                   bg-brand-medium/10 text-brand-dark hover:bg-brand-medium/20
                                   dark:bg-brand-medium/20 dark:text-brand-light dark:hover:bg-brand-medium/30
                                   transition"
                            title="Download the chart as a PNG image">
                        <i class="fa-solid fa-image"></i> PNG
                    </button>

                    <!-- Print -->
                    <button type="button" id="printStatsBtn"
                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold rounded-lg
                                   bg-slate-100 text-slate-700 hover:bg-slate-200
                                   dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700
                                   transition"
                            title="Print this statistics card">
                        <i class="fa-solid fa-print"></i> Print
                    </button>
                </div>
            </div>

            <!-- 2-column layout: chart (left) + status breakdown (right) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- LEFT: chart + mini tiles -->
                <div class="lg:col-span-2">
                    <!-- Mini numeric tiles -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
                        <?php
                        $periods = [
                            'today' => 'Today',
                            'week'  => 'This Week',
                            'month' => 'This Month',
                            'year'  => 'This Year',
                        ];
                        foreach ($periods as $key => $label): ?>
                            <div class="rounded-lg border border-slate-200 dark:border-slate-800 p-3 bg-slate-50 dark:bg-slate-800/40">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500"><?= $label ?></p>
                                <div class="flex items-baseline gap-3 mt-1">
                                    <span class="text-lg font-black text-brand-dark dark:text-brand-medium">
                                        <?= $stats['burials'][$key] ?>
                                        <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase ml-1">burials</span>
                                    </span>
                                    <span class="text-lg font-black text-indigo-600 dark:text-indigo-400">
                                        <?= $stats['deceased'][$key] ?>
                                        <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase ml-1">deceased</span>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Chart canvas -->
                    <div style="height: 320px;">
                        <canvas id="statsChart"></canvas>
                    </div>
                </div>

                <!-- RIGHT: status breakdown -->
                <div class="lg:col-span-1 lg:border-l border-slate-200 dark:border-slate-800 lg:pl-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <i class="fa-solid fa-layer-group mr-1"></i> Lot Status
                        </h3>
                        <span class="text-xs font-bold text-slate-400 dark:text-slate-500">
                            <?= $totalLots ?> total
                        </span>
                    </div>

                    <!-- Status rows -->
                    <div class="space-y-4">
                        <?php foreach ($statusMeta as $status => $meta): ?>
                            <?php
                            $count = $statusCounts[$status] ?? 0;
                            $pct   = $totalLots > 0 ? round(($count / $totalLots) * 100, 1) : 0;
                            ?>
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="inline-flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-slate-300">
                                        <span class="w-2.5 h-2.5 rounded-full <?= $meta['bar'] ?>"></span>
                                        <?= $meta['label'] ?>
                                    </span>
                                    <span class="text-sm font-black text-slate-800 dark:text-white">
                                        <?= $count ?>
                                        <span class="text-xs font-bold text-slate-400 dark:text-slate-500 ml-1">
                                            (<?= $pct ?>%)
                                        </span>
                                    </span>
                                </div>
                                <div class="w-full h-2.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                    <div class="<?= $meta['bar'] ?> h-full rounded-full transition-all duration-700"
                                         style="width: <?= $pct ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Donut chart -->
                    <div class="mt-6 pt-6 border-t border-slate-200 dark:border-slate-800">
                        <div style="height: 180px;">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Burials -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <h2 class="font-bold text-slate-800 dark:text-white">Recent Burials</h2>
                <a href="<?= $basePath ?>pages/cemetery/burials.php" 
                   class="text-xs font-bold text-brand-medium hover:text-brand-dark dark:hover:text-brand-light transition">
                    View All <i class="fa-solid fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3 text-left">Deceased</th>
                            <th class="px-6 py-3 text-left">Cemetery</th>
                            <th class="px-6 py-3 text-left">Lot</th>
                            <th class="px-6 py-3 text-left">Burial Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php if ($recent): ?>
                            <?php foreach ($recent as $b): ?>
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                    <td class="px-6 py-3 font-medium text-slate-700 dark:text-slate-300">
                                        <?= htmlspecialchars($b['first_name'] . ' ' . $b['last_name']) ?>
                                    </td>
                                    <td class="px-6 py-3 text-slate-600 dark:text-slate-400">
                                        <?= htmlspecialchars($b['cemetery_name']) ?>
                                    </td>
                                    <td class="px-6 py-3 text-slate-600 dark:text-slate-400">
                                        <?= htmlspecialchars($b['section'] . '-' . $b['row_num'] . '-' . $b['lot_number']) ?>
                                    </td>
                                    <td class="px-6 py-3 text-slate-600 dark:text-slate-400">
                                        <?= htmlspecialchars($b['burial_date']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="px-6 py-8 text-center text-slate-400 dark:text-slate-500">No burials recorded yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    const stats = <?= json_encode($stats, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const statusCounts = <?= json_encode($statusCounts, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    // Theme-aware colors
    const isDark    = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#cbd5e1' : '#475569';
    const gridColor = isDark ? 'rgba(148,163,184,0.15)' : 'rgba(100,116,139,0.15)';

    // ---------- Line chart ----------
    const canvas = document.getElementById('statsChart');
    let lineChart = null;

    if (canvas) {
        const ctx = canvas.getContext('2d');

        const burialGradient = ctx.createLinearGradient(0, 0, 0, 320);
        burialGradient.addColorStop(0, 'rgba(15, 118, 110, 0.35)');
        burialGradient.addColorStop(1, 'rgba(15, 118, 110, 0.00)');

        const deceasedGradient = ctx.createLinearGradient(0, 0, 0, 320);
        deceasedGradient.addColorStop(0, 'rgba(99, 102, 241, 0.35)');
        deceasedGradient.addColorStop(1, 'rgba(99, 102, 241, 0.00)');

        lineChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Today', 'This Week', 'This Month', 'This Year'],
                datasets: [
                    {
                        label: 'Burials',
                        data: [stats.burials.today, stats.burials.week, stats.burials.month, stats.burials.year],
                        borderColor:     'rgba(15, 118, 110, 1)',
                        backgroundColor: burialGradient,
                        pointBackgroundColor: 'rgba(15, 118, 110, 1)',
                        pointBorderColor:     isDark ? '#0f172a' : '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        borderWidth: 3,
                        fill: true,
                        tension: 0.35
                    },
                    {
                        label: 'Deceased',
                        data: [stats.deceased.today, stats.deceased.week, stats.deceased.month, stats.deceased.year],
                        borderColor:     'rgba(99, 102, 241, 1)',
                        backgroundColor: deceasedGradient,
                        pointBackgroundColor: 'rgba(99, 102, 241, 1)',
                        pointBorderColor:     isDark ? '#0f172a' : '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        borderWidth: 3,
                        fill: true,
                        tension: 0.35
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 800, easing: 'easeOutQuart' },
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: textColor,
                            font: { weight: 'bold', size: 12 },
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 16
                        }
                    },
                    tooltip: {
                        backgroundColor: isDark ? '#0f172a' : '#1e293b',
                        titleColor: '#fff',
                        bodyColor:  '#e2e8f0',
                        padding: 10,
                        cornerRadius: 8,
                        displayColors: true,
                        callbacks: {
                            label: function (context) {
                                return ' ' + context.dataset.label + ': ' + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: { color: textColor, font: { weight: 'bold' } },
                        grid:  { color: gridColor, drawBorder: false }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { color: textColor, precision: 0, stepSize: 1 },
                        grid:  { color: gridColor, drawBorder: false }
                    }
                }
            }
        });
    }

    // ---------- Status donut ----------
    const statusCanvas = document.getElementById('statusChart');
    if (statusCanvas) {
        const statusColors = {
            available: 'rgba(16, 185, 129, 0.85)',
            reserved:  'rgba(245, 158, 11, 0.85)',
            occupied:  'rgba(244, 63, 94, 0.85)'
        };
        const labels = ['Available', 'Reserved', 'Occupied'];
        const values = [statusCounts.available, statusCounts.reserved, statusCounts.occupied];
        const colors = [statusColors.available, statusColors.reserved, statusColors.occupied];
        const total  = values.reduce((a, b) => a + b, 0);

        new Chart(statusCanvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors,
                    borderColor: isDark ? '#0f172a' : '#ffffff',
                    borderWidth: 3,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                animation: { duration: 800, easing: 'easeOutQuart' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: isDark ? '#0f172a' : '#1e293b',
                        titleColor: '#fff',
                        bodyColor:  '#e2e8f0',
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function (context) {
                                const val = context.parsed;
                                const pct = total > 0 ? ((val / total) * 100).toFixed(1) : '0.0';
                                return ' ' + context.label + ': ' + val + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // ---------- PNG Export ----------
    const pngBtn = document.getElementById('exportPngBtn');
    if (pngBtn && lineChart) {
        pngBtn.addEventListener('click', function () {
            const source = lineChart.toBase64Image('image/png', 1);

            const img = new Image();
            img.onload = function () {
                const tmp = document.createElement('canvas');
                tmp.width  = img.width;
                tmp.height = img.height;
                const tctx = tmp.getContext('2d');
                tctx.fillStyle = isDark ? '#0f172a' : '#ffffff';
                tctx.fillRect(0, 0, tmp.width, tmp.height);
                tctx.drawImage(img, 0, 0);

                const link = document.createElement('a');
                link.href = tmp.toDataURL('image/png');
                link.download = 'cemetery_statistics_' + new Date().toISOString().slice(0, 10) + '.png';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            };
            img.src = source;
        });
    }

    // ---------- Print ----------
    const printBtn = document.getElementById('printStatsBtn');
    if (printBtn && lineChart) {
        printBtn.addEventListener('click', function () {
            const chartImage = lineChart.toBase64Image('image/png', 1);
            const w = window.open('', '_blank', 'width=900,height=700');
            if (!w) return;

            const statusRows = [
                ['Available', statusCounts.available],
                ['Reserved',  statusCounts.reserved],
                ['Occupied',  statusCounts.occupied]
            ];
            const totalLots = statusRows.reduce((s, r) => s + r[1], 0);

            w.document.write('<!DOCTYPE html><html><head><title>Cemetery Statistics</title>');
            w.document.write('<style>');
            w.document.write('body{font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;padding:24px;color:#1e293b}');
            w.document.write('h1{font-size:18px;margin:0 0 4px 0}');
            w.document.write('h2{font-size:14px;margin:24px 0 8px 0}');
            w.document.write('p.sub{font-size:11px;color:#64748b;margin:0 0 20px 0}');
            w.document.write('table{border-collapse:collapse;width:100%;margin-bottom:16px;font-size:13px}');
            w.document.write('th,td{border:1px solid #cbd5e1;padding:8px 12px;text-align:left}');
            w.document.write('th{background:#f1f5f9}');
            w.document.write('img{max-width:100%;height:auto;border:1px solid #e2e8f0;border-radius:8px}');
            w.document.write('</style></head><body>');
            w.document.write('<h1>Cemetery &amp; Burial Statistics</h1>');
            w.document.write('<p class="sub">Generated on ' + new Date().toLocaleString() + '</p>');

            w.document.write('<h2>Burial &amp; Deceased</h2>');
            w.document.write('<table><thead><tr><th>Period</th><th>Burials</th><th>Deceased</th></tr></thead><tbody>');
            [
                ['Today',      stats.burials.today, stats.deceased.today],
                ['This Week',  stats.burials.week,  stats.deceased.week],
                ['This Month', stats.burials.month, stats.deceased.month],
                ['This Year',  stats.burials.year,  stats.deceased.year]
            ].forEach(r => {
                w.document.write('<tr><td>' + r[0] + '</td><td>' + r[1] + '</td><td>' + r[2] + '</td></tr>');
            });
            w.document.write('</tbody></table>');

            w.document.write('<h2>Lot Status Breakdown</h2>');
            w.document.write('<table><thead><tr><th>Status</th><th>Count</th><th>Percentage</th></tr></thead><tbody>');
            statusRows.forEach(r => {
                const pct = totalLots > 0 ? ((r[1] / totalLots) * 100).toFixed(1) : '0.0';
                w.document.write('<tr><td>' + r[0] + '</td><td>' + r[1] + '</td><td>' + pct + '%</td></tr>');
            });
            w.document.write('<tr><td><strong>Total</strong></td><td><strong>' + totalLots + '</strong></td><td><strong>100%</strong></td></tr>');
            w.document.write('</tbody></table>');

            w.document.write('<img src="' + chartImage + '" alt="Chart" />');
            w.document.write('</body></html>');
            w.document.close();

            w.onload = function () {
                w.focus();
                w.print();
            };
        });
    }
})();
</script>

<?php include $basePath . 'includes/footer.php'; ?>
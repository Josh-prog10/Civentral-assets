<?php
declare(strict_types=1);

$basePath = '../../';

require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/LguService.php';

use App\Service\LguService;

$lguService = new LguService($pdo);

if (!$isSuperAdmin && !$hasResourceAccess(['lgu', 'facility'])) {
    header('Location: ' . $basePath . 'pages/dashboard.php?error=access_denied');
    exit;
}

/* ---------------- Filters (shared by table + export) ---------------- */
$filters = [];
if (isset($_GET['facility_id']) && is_numeric($_GET['facility_id']) && (int)$_GET['facility_id'] > 0) {
    $filters['facility_id'] = (int)$_GET['facility_id'];
}
if (isset($_GET['status']) && in_array($_GET['status'], ['pending','approved','rejected','cancelled','completed'], true)) {
    $filters['status'] = $_GET['status'];
}
if (isset($_GET['payment_status']) && in_array($_GET['payment_status'], ['unpaid','paid','refunded'], true)) {
    $filters['payment_status'] = $_GET['payment_status'];
}
if (!empty($_GET['date_from'])) {
    $filters['start_date_from'] = $_GET['date_from'] . ' 00:00:00';
}
if (!empty($_GET['date_to'])) {
    $filters['start_date_to'] = $_GET['date_to'] . ' 23:59:59';
}

/* ---------------- CSV export ---------------- */
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $rows = $lguService->getReservations($filters);

    $filename = 'lgu_reservations_' . date('Ymd_His') . '.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $out = fopen('php://output', 'w');

    // UTF-8 BOM so Excel opens it correctly
    fwrite($out, "\xEF\xBB\xBF");

    fputcsv($out, [
        'ID', 'Facility', 'Reserved To', 'Purpose',
        'Start', 'End', 'Status', 'Payment Status',
        'Payment Amount', 'Receipt Number'
    ]);

    foreach ($rows as $r) {
        fputcsv($out, [
            $r['id'],
            $r['facility_name'],
            (string)$r['user_name'],
            $r['purpose'],
            $r['start_datetime'],
            $r['end_datetime'],
            $r['status'],
            $r['payment_status'] ?? 'unpaid',
            $r['payment_amount'] ?? '',
            $r['receipt_number'] ?? '',
        ]);
    }

    fclose($out);
    exit;
}

$stats        = $lguService->getStats();
$reservations = $lguService->getReservations($filters);
$facilities   = $lguService->getFacilities(true);

/* ---------------- Chart data (last 30 days) ---------------- */
$trend = $lguService->getReservationsTrend(30, $filters['facility_id'] ?? null);

$chartLabels    = array_map(fn($r) => date('M j', strtotime($r['d'])), $trend);
$chartTotal     = array_map(fn($r) => (int)$r['total'],     $trend);
$chartApproved  = array_map(fn($r) => (int)$r['approved'],  $trend);
$chartPending   = array_map(fn($r) => (int)$r['pending'],   $trend);
$chartCancelled = array_map(fn($r) => (int)$r['cancelled'], $trend);
$chartCompleted = array_map(fn($r) => (int)$r['completed'], $trend);
$chartRejected  = array_map(fn($r) => (int)$r['rejected'],  $trend);

/* ---------------- Status breakdown (donut chart data) ---------------- */
$breakdownTotal = array_sum($chartTotal);

// Order matches the legend in the design: Pending, Approved, Completed, Cancelled, Rejected
$breakdown = [
    ['label' => 'Pending',   'value' => array_sum($chartPending),   'hex' => '#f59e0b'],
    ['label' => 'Approved',  'value' => array_sum($chartApproved),  'hex' => '#10b981'],
    ['label' => 'Completed', 'value' => array_sum($chartCompleted), 'hex' => '#3b82f6'],
    ['label' => 'Cancelled', 'value' => array_sum($chartCancelled), 'hex' => '#94a3b8'],
    ['label' => 'Rejected',  'value' => array_sum($chartRejected),  'hex' => '#ef4444'],
];

$breakdownLabels = array_column($breakdown, 'label');
$breakdownValues = array_column($breakdown, 'value');
$breakdownColors = array_column($breakdown, 'hex');

/* ---------------- Export URL keeps current filters ---------------- */
$exportQuery = $_GET;
$exportQuery['export'] = 'csv';
$exportUrl = '?' . http_build_query($exportQuery);

$pageTitle = 'LGU Facility Reservation Dashboard';
include $basePath . 'includes/header.php';
include $basePath . 'includes/sidebar.php';
?>

<!-- Chart.js (adjust path if already bundled) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<div class="flex-1 p-6 lg:p-8">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight mb-6">
            <i class="fa-solid fa-building-columns text-brand-medium mr-3"></i>LGU Facility Reservation
        </h1>

        <!-- ================= STATS ================= -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Active Facilities</p>
                <p class="text-2xl font-black text-slate-800 dark:text-white mt-1"><?= $stats['active_facilities'] ?></p>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Today's Reservations</p>
                <p class="text-2xl font-black text-slate-800 dark:text-white mt-1"><?= $stats['today_reservations'] ?></p>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Pending Approvals</p>
                <p class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1"><?= $stats['pending_approvals'] ?></p>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Upcoming (7 days)</p>
                <p class="text-2xl font-black text-brand-dark dark:text-brand-medium mt-1"><?= $stats['upcoming_events'] ?></p>
            </div>
        </div>

        <!-- ================= CHART + STATUS BREAKDOWN ================= -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

            <!-- Chart (2/3) -->
            <div class="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between flex-wrap gap-2">
                    <h2 class="font-bold text-slate-800 dark:text-white">
                        <i class="fa-solid fa-chart-line text-brand-medium mr-2"></i>Reservations Trend (Last 30 Days)
                    </h2>
                    <?php if (!empty($filters['facility_id'])): ?>
                        <span class="text-xs text-slate-500 dark:text-slate-400">
                            Filtered by facility #<?= (int)$filters['facility_id'] ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="p-4 lg:p-6">
                    <div class="relative" style="height: 320px;">
                        <canvas id="lguReservationsChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Status breakdown (1/3) — donut -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm flex flex-col">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800">
                    <h2 class="font-bold text-slate-800 dark:text-white">Status Breakdown</h2>
                </div>

                <div class="p-6 flex-1 flex flex-col items-center justify-center">
                    <div class="relative w-full" style="height: 220px;">
                        <canvas id="lguStatusDonut"></canvas>
                        <?php if ($breakdownTotal === 0): ?>
                            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                <span class="text-xs text-slate-400 dark:text-slate-500">No data</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= FILTERS ================= -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm mb-6">
            <form method="get" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Facility</label>
                    <select name="facility_id" class="px-3 py-1.5 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                        <option value="">All</option>
                        <?php foreach ($facilities as $f): ?>
                            <option value="<?= $f['id'] ?>" <?= (($filters['facility_id'] ?? null) == $f['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($f['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Status</label>
                    <select name="status" class="px-3 py-1.5 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                        <option value="">All</option>
                        <?php foreach (['pending','approved','rejected','cancelled','completed'] as $s): ?>
                            <option value="<?= $s ?>" <?= (($filters['status'] ?? '') === $s) ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Payment</label>
                    <select name="payment_status" class="px-3 py-1.5 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                        <option value="">All</option>
                        <option value="unpaid"   <?= (($filters['payment_status'] ?? '') === 'unpaid')   ? 'selected' : '' ?>>Unpaid</option>
                        <option value="paid"     <?= (($filters['payment_status'] ?? '') === 'paid')     ? 'selected' : '' ?>>Paid</option>
                        <option value="refunded" <?= (($filters['payment_status'] ?? '') === 'refunded') ? 'selected' : '' ?>>Refunded</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Date From</label>
                    <input type="date" name="date_from" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>"
                        class="px-3 py-1.5 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Date To</label>
                    <input type="date" name="date_to" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>"
                        class="px-3 py-1.5 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                </div>

                <button type="submit" class="px-4 py-1.5 bg-brand-medium text-white text-sm font-bold rounded-lg hover:bg-brand-dark transition">
                    <i class="fa-solid fa-filter mr-1"></i> Filter
                </button>

                <a href="<?= $basePath ?>pages/lgu-facilities/ldb.php"
                   class="px-4 py-1.5 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-bold rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition">
                    Clear
                </a>

                <a href="<?= htmlspecialchars($exportUrl) ?>"
                   class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-lg transition shadow-sm">
                    <i class="fa-solid fa-download mr-1"></i> Download CSV
                </a>
            </form>

            <p class="text-xs text-slate-400 dark:text-slate-500 mt-3">
                Showing <strong><?= count($reservations) ?></strong> reservation<?= count($reservations) === 1 ? '' : 's' ?>.
                The download respects the filters above.
            </p>
        </div>

        <!-- ================= TABLE ================= -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <h2 class="font-bold text-slate-800 dark:text-white">All Reservations</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3 text-left">#</th>
                            <th class="px-6 py-3 text-left">Purpose</th>
                            <th class="px-6 py-3 text-left">Facility</th>
                            <th class="px-6 py-3 text-left">Reserve To</th>
                            <th class="px-6 py-3 text-left">Start</th>
                            <th class="px-6 py-3 text-left">End</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-left">Payment</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php if ($reservations): ?>
                            <?php foreach ($reservations as $r): ?>
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                    <td class="px-6 py-3 font-mono text-slate-600 dark:text-slate-400"><?= $r['id'] ?></td>
                                    <td class="px-6 py-3 font-medium text-slate-700 dark:text-slate-300"><?= htmlspecialchars($r['purpose']) ?></td>
                                    <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= htmlspecialchars($r['facility_name']) ?></td>
                                    <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= htmlspecialchars((string)$r['user_name']) ?></td>
                                    <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= date('M d, Y H:i', strtotime($r['start_datetime'])) ?></td>
                                    <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= date('M d, Y H:i', strtotime($r['end_datetime'])) ?></td>
                                    <td class="px-6 py-3">
                                        <?php
                                        $isExpired  = ($r['status'] === 'cancelled' && ($r['cancelled_reason'] ?? '') === 'expired');
                                        $statusText = $isExpired ? 'Auto-cancelled' : ucfirst($r['status']);
                                        $statusClass = match ($r['status']) {
                                            'pending'   => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                                            'approved'  => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                                            'rejected'  => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300',
                                            'cancelled' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400',
                                            'completed' => 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300',
                                            default     => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
                                        };
                                        ?>
                                        <span class="inline-block px-2 py-0.5 text-xs font-bold rounded-full <?= $statusClass ?>">
                                            <?= $statusText ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-3">
                                        <?php
                                        $pStatus = $r['payment_status'] ?? 'unpaid';
                                        $pClass  = match ($pStatus) {
                                            'paid'     => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                                            'refunded' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400',
                                            default    => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                                        };
                                        ?>
                                        <span class="inline-block px-2 py-0.5 text-xs font-bold rounded-full <?= $pClass ?>">
                                            <?= ucfirst($pStatus) ?>
                                        </span>
                                        <?php if (!empty($r['payment_amount']) && (float)$r['payment_amount'] > 0): ?>
                                            <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">₱<?= number_format((float)$r['payment_amount'], 2) ?></div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-slate-400 dark:text-slate-500">
                                    No reservations found for the selected filters.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const isDark = document.documentElement.classList.contains('dark');

    const gridColor  = isDark ? 'rgba(148,163,184,0.15)' : 'rgba(148,163,184,0.25)';
    const tickColor  = isDark ? '#94a3b8' : '#64748b';
    const legendColor = isDark ? '#e2e8f0' : '#334155';

    /* ============ Line chart ============ */
    const lineCanvas = document.getElementById('lguReservationsChart');
    if (lineCanvas) {
        const labels    = <?= json_encode($chartLabels) ?>;
        const totals    = <?= json_encode($chartTotal) ?>;
        const approved  = <?= json_encode($chartApproved) ?>;
        const pending   = <?= json_encode($chartPending) ?>;
        const cancelled = <?= json_encode($chartCancelled) ?>;
        const completed = <?= json_encode($chartCompleted) ?>;

        new Chart(lineCanvas, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Total',
                        data: totals,
                        borderColor: '#0f5c73',
                        backgroundColor: 'rgba(15, 92, 115, 0.10)',
                        borderWidth: 2.5,
                        tension: 0.35,
                        fill: true,
                        pointRadius: 2,
                        pointHoverRadius: 5,
                    },
                    {
                        label: 'Approved',
                        data: approved,
                        borderColor: '#10b981',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        tension: 0.35,
                        pointRadius: 2,
                    },
                    {
                        label: 'Pending',
                        data: pending,
                        borderColor: '#f59e0b',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        tension: 0.35,
                        pointRadius: 2,
                    },
                    {
                        label: 'Completed',
                        data: completed,
                        borderColor: '#0ea5e9',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        tension: 0.35,
                        pointRadius: 2,
                    },
                    {
                        label: 'Cancelled',
                        data: cancelled,
                        borderColor: '#94a3b8',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        borderDash: [5, 4],
                        tension: 0.35,
                        pointRadius: 2,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: legendColor,
                            boxWidth: 12,
                            boxHeight: 12,
                            usePointStyle: true,
                            font: { size: 11, weight: '600' }
                        }
                    },
                    tooltip: {
                        backgroundColor: isDark ? '#0f172a' : '#ffffff',
                        titleColor: isDark ? '#e2e8f0' : '#0f172a',
                        bodyColor:  isDark ? '#cbd5e1' : '#334155',
                        borderColor: gridColor,
                        borderWidth: 1,
                        padding: 10,
                    }
                },
                scales: {
                    x: {
                        grid: { color: gridColor, drawBorder: false },
                        ticks: { color: tickColor, maxRotation: 0, autoSkip: true, maxTicksLimit: 10, font: { size: 10 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor, drawBorder: false },
                        ticks: { color: tickColor, precision: 0, font: { size: 10 } }
                    }
                }
            }
        });
    }

    /* ============ Status donut ============ */
    const donutCanvas = document.getElementById('lguStatusDonut');
    if (donutCanvas) {
        const donutLabels = <?= json_encode($breakdownLabels) ?>;
        const donutValues = <?= json_encode($breakdownValues) ?>;
        const donutColors = <?= json_encode($breakdownColors) ?>;
        const donutTotal  = <?= (int)$breakdownTotal ?>;

        // If there's no data at all, show a single gray placeholder ring
        const data    = donutTotal > 0 ? donutValues : [1];
        const colors  = donutTotal > 0 ? donutColors : ['#e2e8f0'];
        const labels  = donutTotal > 0 ? donutLabels : ['No data'];

        new Chart(donutCanvas, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data,
                    backgroundColor: colors,
                    borderWidth: 0,
                    hoverOffset: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: legendColor,
                            boxWidth: 12,
                            boxHeight: 12,
                            padding: 12,
                            usePointStyle: false,
                            font: { size: 11, weight: '600' },
                            generateLabels: function (chart) {
                                const ds = chart.data.datasets[0];
                                return chart.data.labels.map((label, i) => ({
                                    text: label,
                                    fillStyle: ds.backgroundColor[i],
                                    strokeStyle: ds.backgroundColor[i],
                                    lineWidth: 0,
                                    hidden: false,
                                    index: i,
                                }));
                            }
                        }
                    },
                    tooltip: {
                        enabled: donutTotal > 0,
                        backgroundColor: isDark ? '#0f172a' : '#ffffff',
                        titleColor: isDark ? '#e2e8f0' : '#0f172a',
                        bodyColor:  isDark ? '#cbd5e1' : '#334155',
                        borderColor: gridColor,
                        borderWidth: 1,
                        padding: 10,
                        callbacks: {
                            label: function (ctx) {
                                const val = ctx.parsed || 0;
                                const pct = donutTotal > 0 ? ((val / donutTotal) * 100).toFixed(1) : 0;
                                return ` ${ctx.label}: ${val} (${pct}%)`;
                            }
                        }
                    }
                }
            }
        });
    }
})();
</script>

<?php include $basePath . 'includes/footer.php'; ?>
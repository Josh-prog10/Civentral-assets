<?php
declare(strict_types=1);

$basePath = '../../';
require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/ParkService.php';

use App\Service\ParkService;

$parkService = new ParkService($pdo);

if (!$isSuperAdmin && !$hasResourceAccess(['parks', 'recreation'])) {
    header('Location: ' . $basePath . 'pages/dashboard.php?error=access_denied');
    exit;
}

/* ------------------------------------------------------------------ */
/* 1. Build filters from the query string                              */
/* ------------------------------------------------------------------ */
$filters = [];
if (!empty($_GET['facility_id']) && is_numeric($_GET['facility_id'])) {
    $filters['facility_id'] = (int)$_GET['facility_id'];
}
if (!empty($_GET['status']) && in_array($_GET['status'], ['pending','approved','cancelled','rejected','completed'], true)) {
    $filters['status'] = $_GET['status'];
}
if (!empty($_GET['payment_status']) && in_array($_GET['payment_status'], ['unpaid','paid','refunded'], true)) {
    $filters['payment_status'] = $_GET['payment_status'];
}
if (!empty($_GET['date_from'])) {
    $filters['date_from'] = $_GET['date_from'] . ' 00:00:00';
}
if (!empty($_GET['date_to'])) {
    $filters['date_to'] = $_GET['date_to'] . ' 23:59:59';
}

$reservations = $parkService->getReservations($filters);

/* ------------------------------------------------------------------ */
/* 2. Export handlers — MUST run before any HTML output                */
/* ------------------------------------------------------------------ */
if (isset($_GET['export'])) {

    $format = strtolower($_GET['export']);
    $stamp  = date('Ymd-His');

    /* ---------- CSV ---------- */
    if ($format === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="park-reservations-' . $stamp . '.csv"');
        header('Cache-Control: no-store');

        $out = fopen('php://output', 'w');
        // UTF-8 BOM so Excel on Windows renders ₱ and accented names correctly
        fwrite($out, "\xEF\xBB\xBF");

        fputcsv($out, [
            'ID', 'Event', 'Facility', 'Reserved To',
            'Start', 'End', 'Status', 'Cancelled Reason',
            'Payment Status', 'Amount', 'Method', 'Reference',
            'Receipt No.', 'Paid At',
        ]);

        foreach ($reservations as $r) {
            fputcsv($out, [
                $r['id'],
                $r['event_name'],
                $r['facility_name'],
                $r['user_name'],
                $r['start_datetime'],
                $r['end_datetime'],
                $r['status'],
                $r['cancelled_reason'] ?? '',
                $r['payment_status'] ?? 'unpaid',
                $r['payment_amount'] ?? '',
                $r['payment_method'] ?? '',
                $r['payment_reference'] ?? '',
                $r['receipt_number'] ?? '',
                $r['paid_at'] ?? '',
            ]);
        }
        fclose($out);
        exit;
    }

    /* ---------- Excel (SpreadsheetML 2003 — opens natively in Excel) ---------- */
    if ($format === 'excel') {
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="park-reservations-' . $stamp . '.xls"');
        header('Cache-Control: no-store');

        $esc = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES | ENT_XML1, 'UTF-8');

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
                xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">' . "\n";
        echo '<Worksheet ss:Name="Reservations"><Table>' . "\n";

        $headers = ['ID','Event','Facility','Reserved To','Start','End','Status',
                    'Cancelled Reason','Payment Status','Amount','Method',
                    'Reference','Receipt No.','Paid At'];
        echo '<Row>';
        foreach ($headers as $h) {
            echo '<Cell><Data ss:Type="String">' . $esc($h) . '</Data></Cell>';
        }
        echo '</Row>' . "\n";

        foreach ($reservations as $r) {
            $row = [
                $r['id'], $r['event_name'], $r['facility_name'], $r['user_name'],
                $r['start_datetime'], $r['end_datetime'], $r['status'],
                $r['cancelled_reason'] ?? '',
                $r['payment_status'] ?? 'unpaid',
                $r['payment_amount'] ?? '',
                $r['payment_method'] ?? '',
                $r['payment_reference'] ?? '',
                $r['receipt_number'] ?? '',
                $r['paid_at'] ?? '',
            ];
            echo '<Row>';
            foreach ($row as $i => $v) {
                // Amount column (index 9) as Number so Excel can sum it
                $type = ($i === 9 && is_numeric($v) && $v !== '') ? 'Number' : 'String';
                echo '<Cell><Data ss:Type="' . $type . '">' . $esc($v) . '</Data></Cell>';
            }
            echo '</Row>' . "\n";
        }

        echo '</Table></Worksheet></Workbook>';
        exit;
    }

    /* ---------- JSON ---------- */
    if ($format === 'json') {
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="park-reservations-' . $stamp . '.json"');
        header('Cache-Control: no-store');
        echo json_encode([
            'generated_at' => date('c'),
            'filters'      => $filters,
            'count'        => count($reservations),
            'reservations' => $reservations,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /* Unknown format → fall through and render the page normally */
}

/* ------------------------------------------------------------------ */
/* 3. Dashboard data                                                   */
/* ------------------------------------------------------------------ */
$stats           = $parkService->getStats();
$trend           = $parkService->getReservationTrend(14);
$statusBreakdown = $parkService->getReservationStatusBreakdown();
$facilities      = $parkService->getFacilities();

/* Build the query strings for each export format, preserving filters */
$baseExportQuery = $_GET;
unset($baseExportQuery['export']);

$csvUrl   = '?' . http_build_query($baseExportQuery + ['export' => 'csv']);
$excelUrl = '?' . http_build_query($baseExportQuery + ['export' => 'excel']);
$jsonUrl  = '?' . http_build_query($baseExportQuery + ['export' => 'json']);

$pageTitle = 'Parks & Recreation Dashboard';
include $basePath . 'includes/header.php';
include $basePath . 'includes/sidebar.php';
?>

<div class="flex-1 p-6 lg:p-8">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight mb-6">
            <i class="fa-solid fa-tree text-brand-medium mr-3"></i>Parks & Recreation
        </h1>

        <!-- Stats -->
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
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Upcoming Events (7 days)</p>
                <p class="text-2xl font-black text-brand-dark dark:text-brand-medium mt-1"><?= $stats['upcoming_events'] ?></p>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-8">
            <div class="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold text-slate-800 dark:text-white">Reservations — Last 14 Days</h2>
                    <span class="text-xs font-bold text-slate-400 dark:text-slate-500">
                        Total: <?= array_sum(array_column($trend, 'count')) ?>
                    </span>
                </div>
                <div class="h-64"><canvas id="trendChart"></canvas></div>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm">
                <h2 class="font-bold text-slate-800 dark:text-white mb-4">Status Breakdown</h2>
                <div class="h-64"><canvas id="statusChart"></canvas></div>
            </div>
        </div>

        <!-- Filter + export bar -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm mb-6">
            <form method="get" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Facility</label>
                    <select name="facility_id" class="px-3 py-1.5 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                        <option value="">All</option>
                        <?php foreach ($facilities as $f): ?>
                            <option value="<?= (int)$f['id'] ?>" <?= (isset($_GET['facility_id']) && (int)$_GET['facility_id'] === (int)$f['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($f['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Status</label>
                    <select name="status" class="px-3 py-1.5 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                        <option value="">All</option>
                        <?php foreach (['pending','approved','completed','cancelled','rejected'] as $s): ?>
                            <option value="<?= $s ?>" <?= (($_GET['status'] ?? '') === $s) ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Payment</label>
                    <select name="payment_status" class="px-3 py-1.5 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                        <option value="">All</option>
                        <option value="unpaid"   <?= (($_GET['payment_status'] ?? '') === 'unpaid')   ? 'selected' : '' ?>>Unpaid</option>
                        <option value="paid"     <?= (($_GET['payment_status'] ?? '') === 'paid')     ? 'selected' : '' ?>>Paid</option>
                        <option value="refunded" <?= (($_GET['payment_status'] ?? '') === 'refunded') ? 'selected' : '' ?>>Refunded</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">From</label>
                    <input type="date" name="date_from" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>"
                           class="px-3 py-1.5 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">To</label>
                    <input type="date" name="date_to" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>"
                           class="px-3 py-1.5 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                </div>
                <button type="submit" class="px-4 py-1.5 bg-brand-medium text-white text-sm font-bold rounded-lg hover:bg-brand-dark transition">
                    <i class="fa-solid fa-filter mr-1"></i>Filter
                </button>
                <a href="<?= $basePath ?>pages/parks/pdb.php" class="px-4 py-1.5 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-bold rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition">
                    Clear
                </a>

                <!-- Export dropdown -->
                <div class="ml-auto relative" id="exportMenu">
                    <button type="button" id="exportToggle"
                            class="px-4 py-1.5 bg-emerald-600 text-white text-sm font-bold rounded-lg hover:bg-emerald-700 transition">
                        <i class="fa-solid fa-download mr-1"></i>Export <i class="fa-solid fa-caret-down ml-1"></i>
                    </button>
                    <div id="exportDropdown"
                         class="hidden absolute right-0 mt-2 w-44 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-lg z-20 overflow-hidden">
                        <a href="<?= htmlspecialchars($csvUrl) ?>"
                           class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                            <i class="fa-solid fa-file-csv text-emerald-600 w-4"></i> CSV (.csv)
                        </a>
                        <a href="<?= htmlspecialchars($excelUrl) ?>"
                           class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                            <i class="fa-solid fa-file-excel text-emerald-700 w-4"></i> Excel (.xls)
                        </a>
                        <a href="<?= htmlspecialchars($jsonUrl) ?>"
                           class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                            <i class="fa-solid fa-file-code text-amber-600 w-4"></i> JSON (.json)
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- All reservations table -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <h2 class="font-bold text-slate-800 dark:text-white">All Reservations</h2>
                <span class="text-xs font-bold text-slate-400 dark:text-slate-500">
                    <?= count($reservations) ?> record<?= count($reservations) === 1 ? '' : 's' ?>
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3 text-left">#</th>
                            <th class="px-6 py-3 text-left">Event</th>
                            <th class="px-6 py-3 text-left">Facility</th>
                            <th class="px-6 py-3 text-left">Reserved To</th>
                            <th class="px-6 py-3 text-left">Start</th>
                            <th class="px-6 py-3 text-left">End</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-left">Payment</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php if ($reservations): ?>
                            <?php foreach ($reservations as $r): ?>
                                <?php
                                $isExpired   = ($r['status'] === 'cancelled' && ($r['cancelled_reason'] ?? '') === 'expired');
                                $statusText  = $isExpired ? 'Auto-cancelled' : ucfirst($r['status']);
                                $statusClass = match ($r['status']) {
                                    'pending'   => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                                    'approved'  => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                                    'cancelled' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400',
                                    'rejected'  => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300',
                                    'completed' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                                    default     => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
                                };
                                $pStatus = $r['payment_status'] ?? 'unpaid';
                                $pClass  = match ($pStatus) {
                                    'paid'     => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                                    'refunded' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400',
                                    default    => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                                };
                                ?>
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                    <td class="px-6 py-3 font-mono text-slate-600 dark:text-slate-400"><?= (int)$r['id'] ?></td>
                                    <td class="px-6 py-3 font-medium text-slate-700 dark:text-slate-300"><?= htmlspecialchars($r['event_name']) ?></td>
                                    <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= htmlspecialchars($r['facility_name']) ?></td>
                                    <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= htmlspecialchars((string)$r['user_name']) ?></td>
                                    <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= date('M d, Y H:i', strtotime($r['start_datetime'])) ?></td>
                                    <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= date('M d, Y H:i', strtotime($r['end_datetime'])) ?></td>
                                    <td class="px-6 py-3">
                                        <span class="inline-block px-2 py-0.5 text-xs font-bold rounded-full <?= $statusClass ?>">
                                            <?= $statusText ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-3">
                                        <span class="inline-block px-2 py-0.5 text-xs font-bold rounded-full <?= $pClass ?>">
                                            <?= ucfirst($pStatus) ?>
                                        </span>
                                        <?php if (!empty($r['payment_amount']) && (float)$r['payment_amount'] > 0): ?>
                                            <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">₱<?= number_format((float)$r['payment_amount'], 2) ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($r['receipt_number'])): ?>
                                            <div class="text-[10px] font-mono text-slate-400 dark:text-slate-500 mt-0.5"><?= htmlspecialchars($r['receipt_number']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-slate-400 dark:text-slate-500">
                                    No reservations match the selected filters.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    /* ---------- Export dropdown toggle ---------- */
    const toggle   = document.getElementById('exportToggle');
    const dropdown = document.getElementById('exportDropdown');

    toggle.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdown.classList.toggle('hidden');
    });
    document.addEventListener('click', () => dropdown.classList.add('hidden'));
    dropdown.addEventListener('click', (e) => e.stopPropagation());

    /* ---------- Charts ---------- */
    const isDark    = document.documentElement.classList.contains('dark')
                   || document.body.classList.contains('dark');
    const gridColor = isDark ? 'rgba(148,163,184,.15)' : 'rgba(148,163,184,.25)';
    const textColor = isDark ? '#cbd5e1' : '#475569';

    Chart.defaults.font.family = "'Segoe UI', Tahoma, Arial, sans-serif";
    Chart.defaults.color = textColor;

    const trendData  = <?= json_encode($trend, JSON_UNESCAPED_UNICODE) ?>;
    const statusData = <?= json_encode($statusBreakdown, JSON_UNESCAPED_UNICODE) ?>;

    const trendCtx = document.getElementById('trendChart').getContext('2d');
    const grad = trendCtx.createLinearGradient(0, 0, 0, 260);
    grad.addColorStop(0, 'rgba(15,92,115,.45)');
    grad.addColorStop(1, 'rgba(15,92,115,.02)');

    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: trendData.map(r => r.label),
            datasets: [{
                label: 'Reservations',
                data: trendData.map(r => r.count),
                borderColor: '#0f5c73',
                backgroundColor: grad,
                fill: true,
                tension: .35,
                borderWidth: 2,
                pointRadius: 3,
                pointBackgroundColor: '#0f5c73',
                pointHoverRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        title: (items) => trendData[items[0].dataIndex].date,
                        label: (item) => ' ' + item.parsed.y + ' reservation' + (item.parsed.y === 1 ? '' : 's')
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: textColor } },
                y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: textColor, precision: 0, stepSize: 1 } }
            }
        }
    });

    const keys    = ['pending', 'approved', 'completed', 'cancelled', 'rejected'];
    const labels  = ['Pending', 'Approved', 'Completed', 'Cancelled', 'Rejected'];
    const colors  = ['#f59e0b', '#10b981', '#3b82f6', '#94a3b8', '#f43f5e'];
    const values  = keys.map(k => statusData[k] || 0);
    const hasData = values.some(v => v > 0);

    new Chart(document.getElementById('statusChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: hasData ? labels : ['No data'],
            datasets: [{
                data: hasData ? values : [1],
                backgroundColor: hasData ? colors : ['#e2e8f0'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '62%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 10, boxHeight: 10, padding: 10, font: { size: 11 }, color: textColor }
                },
                tooltip: {
                    enabled: hasData,
                    callbacks: { label: (item) => ' ' + item.label + ': ' + item.parsed }
                }
            }
        }
    });
})();
</script>

<?php include $basePath . 'includes/footer.php'; ?>
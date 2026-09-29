<?php
declare(strict_types=1);

$basePath = '../../';
require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/WaterDrainageService.php';

use App\Service\WaterDrainageService;

$wdService = new WaterDrainageService($pdo);

if (!$isSuperAdmin && !$hasResourceAccess(['water_drainage', 'water drainage', 'water supply', 'drainage'])) {
    header('Location: ' . $basePath . 'pages/dashboard.php?error=access_denied');
    exit;
}

$stats  = $wdService->getStats();
$recent = $wdService->getRequests(['limit' => 5]);

$trends = [
    'today' => $wdService->getTrendData('today'),
    'week'  => $wdService->getTrendData('week'),
    'month' => $wdService->getTrendData('month'),
    'year'  => $wdService->getTrendData('year'),
];

$pageTitle = 'Water & Drainage Dashboard';
include $basePath . 'includes/header.php';
include $basePath . 'includes/sidebar.php';
?>

<div class="flex-1 p-6 lg:p-8">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight mb-6">
            <i class="fa-solid fa-water text-brand-medium mr-3"></i>Water Supply & Drainage Requests
        </h1>

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-8">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total</p>
                <p class="text-2xl font-black text-slate-800 dark:text-white mt-1"><?= $stats['total'] ?></p>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Pending</p>
                <p class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1"><?= $stats['pending'] ?></p>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Assigned</p>
                <p class="text-2xl font-black text-blue-600 dark:text-blue-400 mt-1"><?= $stats['assigned'] ?></p>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">In Progress</p>
                <p class="text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-1"><?= $stats['in_progress'] ?></p>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Resolved</p>
                <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1"><?= $stats['resolved'] ?></p>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Today New</p>
                <p class="text-2xl font-black text-brand-dark dark:text-brand-medium mt-1"><?= $stats['today_new'] ?></p>
            </div>
        </div>

        <!-- ===================== Trend Line Chart ===================== -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm mb-8">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
                <div>
                    <h2 class="font-bold text-slate-800 dark:text-white">
                        <i class="fa-solid fa-chart-line text-brand-medium mr-2"></i>Requests Submitted
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Trend of water &amp; drainage requests over time</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <div id="trendPeriodGroup" class="inline-flex rounded-lg border border-slate-300 dark:border-slate-700 overflow-hidden">
                        <button type="button" data-period="today"
                                class="trend-btn px-3 py-1.5 text-xs font-bold transition">Today</button>
                        <button type="button" data-period="week"
                                class="trend-btn px-3 py-1.5 text-xs font-bold transition border-l border-slate-300 dark:border-slate-700">Week</button>
                        <button type="button" data-period="month"
                                class="trend-btn px-3 py-1.5 text-xs font-bold transition border-l border-slate-300 dark:border-slate-700">Month</button>
                        <button type="button" data-period="year"
                                class="trend-btn px-3 py-1.5 text-xs font-bold transition border-l border-slate-300 dark:border-slate-700">Year</button>
                    </div>

                    <button type="button" id="exportCsvBtn"
                            class="px-3 py-1.5 text-xs font-bold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-sm">
                        <i class="fa-solid fa-file-csv mr-1"></i> CSV
                    </button>
                    <button type="button" id="exportPngBtn"
                            class="px-3 py-1.5 text-xs font-bold rounded-lg bg-brand-dark hover:bg-[#0e4f62] text-white transition shadow-sm">
                        <i class="fa-solid fa-image mr-1"></i> PNG
                    </button>
                </div>
            </div>

            <div class="relative" style="height: 320px;">
                <canvas id="requestsTrendChart"></canvas>
            </div>

            <div class="mt-4 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500 dark:text-slate-400">
                <span>Total for selected period: <strong id="trendTotal" class="text-slate-800 dark:text-white">0</strong></span>
                <span id="trendRangeLabel"></span>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800">
                <h2 class="font-bold text-slate-800 dark:text-white">Recent Requests</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3 text-left">#</th>
                            <th class="px-6 py-3 text-left">Title</th>
                            <th class="px-6 py-3 text-left">Category</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-left">Submitted By</th>
                            <th class="px-6 py-3 text-left">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php if ($recent): ?>
                            <?php foreach ($recent as $r): ?>
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                    <td class="px-6 py-3 font-mono text-slate-600 dark:text-slate-400"><?= $r['id'] ?></td>
                                    <td class="px-6 py-3 font-medium text-slate-700 dark:text-slate-300">
                                        <a href="<?= $basePath ?>pages/water-drainage/view.php?id=<?= $r['id'] ?>" class="text-brand-medium hover:underline"><?= htmlspecialchars($r['title']) ?></a>
                                    </td>
                                    <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= htmlspecialchars($r['category_name']) ?></td>
                                    <td class="px-6 py-3">
                                        <span class="inline-block px-2 py-0.5 text-xs font-bold rounded-full
                                            <?php
                                            $statusClass = match ($r['status']) {
                                                'pending'     => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                                                'assigned'    => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                                                'in_progress' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
                                                'resolved'    => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                                                'rejected'    => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300',
                                                default       => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
                                            };
                                            echo $statusClass;
                                            ?>">
                                            <?= ucfirst(str_replace('_', ' ', $r['status'])) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= htmlspecialchars((string)$r['submitter_name']) ?></td>
                                    <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="px-6 py-8 text-center text-slate-400 dark:text-slate-500">No requests yet.</td></tr>
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
    const TREND_DATA = <?= json_encode($trends, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const ctx = document.getElementById('requestsTrendChart').getContext('2d');
    let currentPeriod = 'week';
    let chart = null;

    const isDark = () =>
        document.documentElement.classList.contains('dark') ||
        document.body.classList.contains('dark');

    function makeGradient(context) {
        const g = context.createLinearGradient(0, 0, 0, 320);
        if (isDark()) {
            g.addColorStop(0, 'rgba(56, 189, 248, 0.45)');
            g.addColorStop(1, 'rgba(56, 189, 248, 0.02)');
        } else {
            g.addColorStop(0, 'rgba(13, 110, 138, 0.35)');
            g.addColorStop(1, 'rgba(13, 110, 138, 0.02)');
        }
        return g;
    }

    function buildChart(period) {
        const payload = TREND_DATA[period];
        if (!payload) return;

        if (chart) {
            chart.destroy();
        }

        const axisColor = isDark() ? '#94a3b8' : '#475569';
        const gridColor = isDark() ? 'rgba(148, 163, 184, 0.15)' : 'rgba(100, 116, 139, 0.12)';
        const lineColor = isDark() ? '#38bdf8' : '#0d6e8a';

        chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: payload.labels,
                datasets: [{
                    label: 'Requests',
                    data: payload.data,
                    borderColor: lineColor,
                    backgroundColor: makeGradient(ctx),
                    borderWidth: 2.5,
                    tension: 0.35,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 6,
                    pointBackgroundColor: lineColor,
                    pointBorderColor: isDark() ? '#0f172a' : '#ffffff',
                    pointBorderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 500, easing: 'easeOutQuart' },
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: isDark() ? '#0f172a' : '#1e293b',
                        titleColor: '#f8fafc',
                        bodyColor: '#e2e8f0',
                        padding: 10,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: (c) => ' ' + c.parsed.y + ' request' + (c.parsed.y === 1 ? '' : 's')
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: { color: axisColor, font: { size: 11, weight: '600' }, maxRotation: 0, autoSkipPadding: 12 },
                        grid: { color: gridColor, drawBorder: false }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { color: axisColor, font: { size: 11, weight: '600' }, precision: 0, stepSize: 1 },
                        grid: { color: gridColor, drawBorder: false }
                    }
                }
            }
        });

        document.getElementById('trendTotal').textContent = payload.total;
        const labels = payload.labels;
        const rangeText = labels.length ? `${labels[0]}  →  ${labels[labels.length - 1]}` : '';
        document.getElementById('trendRangeLabel').textContent = rangeText;
    }

    function setActiveButton(period) {
        document.querySelectorAll('.trend-btn').forEach(btn => {
            const active = btn.dataset.period === period;
            btn.setAttribute('aria-pressed', active ? 'true' : 'false');
            if (active) {
                btn.classList.add('bg-brand-dark', 'text-white');
                btn.classList.remove('bg-white', 'dark:bg-slate-800', 'text-slate-600', 'dark:text-slate-300');
            } else {
                btn.classList.remove('bg-brand-dark', 'text-white');
                btn.classList.add('bg-white', 'dark:bg-slate-800', 'text-slate-600', 'dark:text-slate-300');
            }
        });
    }

    function selectPeriod(period) {
        if (!TREND_DATA[period]) return;
        currentPeriod = period;
        setActiveButton(period);
        buildChart(period);
    }

    document.querySelectorAll('.trend-btn').forEach(btn => {
        btn.addEventListener('click', () => selectPeriod(btn.dataset.period));
    });

    // Re-render on dark-mode toggle
    const themeObserver = new MutationObserver(() => buildChart(currentPeriod));
    themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    themeObserver.observe(document.body, { attributes: true, attributeFilter: ['class'] });

    // ---------- Export CSV ----------
    document.getElementById('exportCsvBtn').addEventListener('click', () => {
        const payload = TREND_DATA[currentPeriod];
        if (!payload) return;
        const rows = [['Period', 'Label', 'Requests']];
        payload.labels.forEach((label, i) => {
            rows.push([payload.period, label, payload.data[i] ?? 0]);
        });
        rows.push(['', 'TOTAL', payload.total]);

        const csv = rows.map(r =>
            r.map(v => {
                const s = String(v ?? '');
                return /[",\n]/.test(s) ? '"' + s.replace(/"/g, '""') + '"' : s;
            }).join(',')
        ).join('\n');

        const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `water_drainage_requests_${currentPeriod}_${new Date().toISOString().slice(0,10)}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    });

    // ---------- Export PNG ----------
    document.getElementById('exportPngBtn').addEventListener('click', () => {
        if (!chart) return;
        const url = chart.toBase64Image('image/png', 1);
        const a = document.createElement('a');
        a.href = url;
        a.download = `water_drainage_requests_${currentPeriod}_${new Date().toISOString().slice(0,10)}.png`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    });

    // Init
    selectPeriod('week');
})();
</script>

<?php include $basePath . 'includes/footer.php'; ?>
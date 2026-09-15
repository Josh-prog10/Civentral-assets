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

$stats = $wdService->getStats();
$recent = $wdService->getRequests(['limit' => 5]);

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

<?php include $basePath . 'includes/footer.php'; ?>
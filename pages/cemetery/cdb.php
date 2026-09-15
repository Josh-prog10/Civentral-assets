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
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8"> <!-- Changed to 5 columns -->
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
            <!-- NEW: Cemeteries count card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Cemeteries</p>
                <p class="text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-1"><?= $totalCemeteries ?></p>
            </div>
        </div>

        <!-- Recent Burials -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <h2 class="font-bold text-slate-800 dark:text-white">Recent Burials</h2>
                <!-- NEW: Link to full burial records -->
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
                            <th class="px-6 py-3 text-left">Cemetery</th>   <!-- NEW -->
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
                                        <?= htmlspecialchars($b['cemetery_name']) ?>   <!-- NEW -->
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

<?php include $basePath . 'includes/footer.php'; ?>
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

$pageTitle = 'Asset Inventory Dashboard';
include $basePath . 'includes/header.php';
include $basePath . 'includes/sidebar.php';
?>

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

<?php include $basePath . 'includes/footer.php'; ?>
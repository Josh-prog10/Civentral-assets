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

$userId = null;
if (!$isSuperAdmin && !$hasResourceAccess(['water_drainage_manage'])) {
    $userId = $headerUser['id'] ?? ($_SESSION['user_id'] ?? null);
}

$filters = [];
if ($userId) {
    $filters['user_id'] = $userId;
}
if (isset($_GET['category_id']) && is_numeric($_GET['category_id'])) {
    $filters['category_id'] = (int)$_GET['category_id'];
}
if (isset($_GET['status']) && in_array($_GET['status'], ['pending','assigned','in_progress','resolved','rejected'], true)) {
    $filters['status'] = $_GET['status'];
}
if (isset($_GET['assigned_to']) && is_numeric($_GET['assigned_to'])) {
    $filters['assigned_to'] = (int)$_GET['assigned_to'];
}

$requests = $wdService->getRequests($filters);
$categories = $wdService->getCategories();
$staff = $wdService->getStaffUsers();

$pageTitle = 'Water/Drainage Requests';
include $basePath . 'includes/header.php';
include $basePath . 'includes/sidebar.php';
?>

<div class="flex-1 p-6 lg:p-8">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">
                <i class="fa-solid fa-list text-brand-medium mr-3"></i>All Requests
            </h1>
            <div class="flex gap-3">
                <a href="<?= $basePath ?>pages/water-drainage/submit.php" class="text-sm font-bold bg-brand-dark hover:bg-[#0e4f62] text-white px-4 py-2 rounded-lg transition shadow-sm">
                    <i class="fa-solid fa-plus mr-1"></i> New Request
                </a>
                <a href="<?= $basePath ?>pages/water-drainage/wdb.php" class="text-sm font-bold text-brand-medium hover:text-brand-dark dark:hover:text-brand-light transition">Back</a>
            </div>
        </div>

        <!-- Filter -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm mb-6 flex flex-wrap gap-3">
            <form method="get" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Category</label>
                    <select name="category_id" class="px-3 py-1.5 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                        <option value="">All</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= (isset($_GET['category_id']) && $_GET['category_id']==$c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Status</label>
                    <select name="status" class="px-3 py-1.5 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                        <option value="">All</option>
                        <option value="pending" <?= (isset($_GET['status']) && $_GET['status']=='pending')?'selected':'' ?>>Pending</option>
                        <option value="assigned" <?= (isset($_GET['status']) && $_GET['status']=='assigned')?'selected':'' ?>>Assigned</option>
                        <option value="in_progress" <?= (isset($_GET['status']) && $_GET['status']=='in_progress')?'selected':'' ?>>In Progress</option>
                        <option value="resolved" <?= (isset($_GET['status']) && $_GET['status']=='resolved')?'selected':'' ?>>Resolved</option>
                        <option value="rejected" <?= (isset($_GET['status']) && $_GET['status']=='rejected')?'selected':'' ?>>Rejected</option>
                    </select>
                </div>
                <?php if ($isSuperAdmin || $hasResourceAccess(['water_drainage_manage'])): ?>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Assigned To</label>
                        <select name="assigned_to" class="px-3 py-1.5 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                            <option value="">All</option>
                            <?php foreach ($staff as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= (isset($_GET['assigned_to']) && $_GET['assigned_to']==$s['id']) ? 'selected' : '' ?>><?= htmlspecialchars($s['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                <button type="submit" class="px-4 py-1.5 bg-brand-medium text-white text-sm font-bold rounded-lg hover:bg-brand-dark transition">Filter</button>
                <a href="<?= $basePath ?>pages/water-drainage/requests.php" class="px-4 py-1.5 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-bold rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition">Clear</a>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3 text-left">#</th>
                            <th class="px-6 py-3 text-left">Title</th>
                            <th class="px-6 py-3 text-left">Category</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-left">Submitted By</th>
                            <th class="px-6 py-3 text-left">Assigned To</th>
                            <th class="px-6 py-3 text-left">Date</th>
                            <th class="px-6 py-3 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php foreach ($requests as $r): ?>
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
                                <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= htmlspecialchars((string)($r['assigned_name'] ?? 'Unassigned')) ?></td>
                                <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
                                <td class="px-6 py-3">
                                    <a href="<?= $basePath ?>pages/water-drainage/view.php?id=<?= $r['id'] ?>" class="text-brand-medium hover:underline text-xs font-bold">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($requests)): ?>
                            <tr><td colspan="8" class="px-6 py-8 text-center text-slate-400 dark:text-slate-500">No requests found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include $basePath . 'includes/footer.php'; ?>
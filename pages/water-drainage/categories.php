<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/WaterDrainageService.php';

use App\Service\WaterDrainageService;

$wdService = new WaterDrainageService($pdo);

if (!$isSuperAdmin && !$hasResourceAccess(['water_drainage_manage'])) {
    header('Location: ' . $basePath . 'pages/dashboard.php?error=access_denied');
    exit;
}

$message = $error = '';
$csrfToken = bin2hex(random_bytes(32));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($csrfToken, $_POST['csrf_token'])) {
        $error = 'Invalid request.';
    } else {
        if (isset($_POST['add'])) {
            $name = trim($_POST['name'] ?? '');
            $desc = trim($_POST['description'] ?? '');
            if ($name === '') {
                $error = 'Category name is required.';
            } else {
                try {
                    if ($wdService->addCategory($name, $desc)) {
                        $message = 'Category added.';
                    } else {
                        $error = 'Add failed (duplicate name?).';
                    }
                } catch (Exception $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        } elseif (isset($_POST['update'])) {
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $desc = trim($_POST['description'] ?? '');
            if ($id < 1 || $name === '') {
                $error = 'Valid ID and name are required.';
            } else {
                try {
                    if ($wdService->updateCategory($id, $name, $desc)) {
                        $message = 'Category updated.';
                    } else {
                        $error = 'Update failed.';
                    }
                } catch (Exception $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        } elseif (isset($_POST['delete'])) {
            $id = (int)($_POST['id'] ?? 0);
            if ($id < 1) {
                $error = 'Invalid category ID.';
            } else {
                try {
                    if ($wdService->deleteCategory($id)) {
                        $message = 'Category deleted.';
                    } else {
                        $error = 'Cannot delete category used in requests.';
                    }
                } catch (Exception $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        }
    }
}

$categories = $wdService->getCategories();
$pageTitle = 'Request Categories';
include $basePath . 'includes/header.php';
include $basePath . 'includes/sidebar.php';
?>

<div class="flex-1 p-6 lg:p-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">
                <i class="fa-solid fa-tags text-brand-medium mr-3"></i>Request Categories
            </h1>
            <a href="<?= $basePath ?>pages/water-drainage/wdb.php" class="text-sm font-bold text-brand-medium hover:text-brand-dark dark:hover:text-brand-light transition">
                <i class="fa-solid fa-arrow-left mr-1"></i> Back
            </a>
        </div>

        <?php if ($message): ?>
            <div class="mb-4 p-3 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 rounded-lg text-sm font-bold"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 rounded-lg text-sm font-bold"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm mb-8">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-4">Add New Category</h2>
            <form method="post" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="text" name="name" placeholder="Category Name" required class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                <input type="text" name="description" placeholder="Description" class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                <button type="submit" name="add" class="px-4 py-2 bg-brand-dark hover:bg-[#0e4f62] text-white font-bold text-sm rounded-lg transition shadow-sm">
                    <i class="fa-solid fa-plus mr-1"></i> Add
                </button>
            </form>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3 text-left">#</th>
                            <th class="px-6 py-3 text-left">Name</th>
                            <th class="px-6 py-3 text-left">Description</th>
                            <th class="px-6 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php foreach ($categories as $c): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                <td class="px-6 py-3 font-mono text-slate-600 dark:text-slate-400"><?= $c['id'] ?></td>
                                <td class="px-6 py-3 font-medium text-slate-700 dark:text-slate-300"><?= htmlspecialchars($c['name']) ?></td>
                                <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= htmlspecialchars($c['description']) ?></td>
                                <td class="px-6 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        <form method="post" class="inline-flex items-center gap-1">
                                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                            <input type="text" name="name" value="<?= htmlspecialchars($c['name']) ?>" class="w-24 px-1 py-0.5 text-xs border border-slate-300 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
                                            <input type="text" name="description" value="<?= htmlspecialchars($c['description']) ?>" class="w-24 px-1 py-0.5 text-xs border border-slate-300 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
                                            <button type="submit" name="update" class="px-2 py-0.5 bg-brand-medium text-white text-xs rounded hover:bg-brand-dark transition">Update</button>
                                        </form>
                                        <form method="post" onsubmit="return confirm('Delete this category?')" class="inline">
                                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                            <button type="submit" name="delete" class="px-2 py-0.5 bg-rose-500 text-white text-xs rounded hover:bg-rose-600 transition">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($categories)): ?>
                            <tr><td colspan="4" class="px-6 py-8 text-center text-slate-400 dark:text-slate-500">No categories.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include $basePath . 'includes/footer.php'; ?>
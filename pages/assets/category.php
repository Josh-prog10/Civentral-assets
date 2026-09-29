<?php
declare(strict_types=1);

$basePath = '../../';
require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/AssetService.php';

use App\Service\AssetService;

$assetService = new AssetService($pdo);

$normalizeAssetIcon = static function ($icon): string {
    $icon = trim((string)$icon);
    if ($icon === '') {
        return 'fa-solid fa-cube';
    }
    if (strpos($icon, 'fa-') === false) {
        $icon = 'fa-' . $icon;
    }
    if (!preg_match('/\bfa-(solid|regular|brands)\b/', $icon)) {
        $icon = 'fa-solid ' . $icon;
    }
    return $icon;
};

if (!$isSuperAdmin && !$hasResourceAccess(['assets_manage', 'asset', 'asset management', 'asset inventory', 'asset inventory tracker'])) {
    header('Location: ' . $basePath . 'pages/dashboard.php?error=access_denied');
    exit;
}

$message = $error = '';

// --- CSRF token ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

// Field options shown in the permission checklist
$fieldOptions = [
    'plate_number'     => ['label' => 'License Plate',     'icon' => 'fa-car'],
    'coordinates'      => ['label' => 'Coordinates',       'icon' => 'fa-crosshairs'],
    'gis_map'          => ['label' => 'GIS Map',           'icon' => 'fa-map-location-dot'],
    'maintenance'      => ['label' => 'Maintenance',       'icon' => 'fa-wrench'],
    'acquisition_date' => ['label' => 'Acquisition Date',  'icon' => 'fa-calendar'],
    'notes'            => ['label' => 'Notes',             'icon' => 'fa-note-sticky'],
];

// Collect checked fields from POST
$collectFieldConfig = static function (): array {
    return [
        'plate_number'     => isset($_POST['field_config']['plate_number']),
        'coordinates'      => isset($_POST['field_config']['coordinates']),
        'gis_map'          => isset($_POST['field_config']['gis_map']),
        'maintenance'      => isset($_POST['field_config']['maintenance']),
        'acquisition_date' => isset($_POST['field_config']['acquisition_date']),
        'notes'            => isset($_POST['field_config']['notes']),
    ];
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = 'Invalid request.';
    } else {
        if (isset($_POST['add'])) {
            $name = trim($_POST['name'] ?? '');
            $desc = trim($_POST['description'] ?? '');
            $icon = $normalizeAssetIcon($_POST['icon'] ?? 'fa-cube');
            $fieldConfig = $collectFieldConfig();
            if ($name === '') {
                $error = 'Category name is required.';
            } else {
                try {
                    if ($assetService->addCategory($name, $desc, $icon, $fieldConfig)) {
                        $message = 'Category added.';
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    } else {
                        $error = 'Add failed (duplicate name?).';
                    }
                } catch (Exception $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        } elseif (isset($_POST['update'])) {
            $id = (int)$_POST['id'];
            $name = trim($_POST['name'] ?? '');
            $desc = trim($_POST['description'] ?? '');
            $icon = $normalizeAssetIcon($_POST['icon'] ?? 'fa-cube');
            $fieldConfig = $collectFieldConfig();
            if ($name === '' || $id < 1) {
                $error = 'Valid category ID and name required.';
            } else {
                try {
                    if ($assetService->updateCategory($id, $name, $desc, $icon, $fieldConfig)) {
                        $message = 'Category updated.';
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    } else {
                        $error = 'Update failed.';
                    }
                } catch (Exception $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        } elseif (isset($_POST['delete'])) {
            $id = (int)$_POST['id'];
            if ($id < 1) {
                $error = 'Invalid category ID.';
            } else {
                try {
                    if ($assetService->deleteCategory($id)) {
                        $message = 'Category deleted.';
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    } else {
                        $error = 'Cannot delete category used in assets.';
                    }
                } catch (Exception $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        }
    }
}

$categories = $assetService->getCategories();
$pageTitle = 'Asset Categories';
include $basePath . 'includes/header.php';
include $basePath . 'includes/sidebar.php';
?>

<div class="flex-1 p-6 lg:p-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">
                <i class="fa-solid fa-tags text-brand-medium mr-3"></i>Asset Categories
            </h1>
            <a href="<?= $basePath ?>pages/assets/adb.php" class="text-sm font-bold text-brand-medium hover:text-brand-dark dark:hover:text-brand-light transition">
                <i class="fa-solid fa-arrow-left mr-1"></i> Back
            </a>
        </div>

        <?php if ($message): ?>
            <div class="mb-4 p-3 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 rounded-lg text-sm font-bold"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 rounded-lg text-sm font-bold"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Add New Category -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm mb-8">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-4">Add New Category</h2>
            <form method="post" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <input type="text" name="name" placeholder="Category Name" required class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                    <input type="text" name="description" placeholder="Description" class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                    <input type="text" name="icon" placeholder="Font Awesome Icon (e.g., fa-cube)" value="fa-cube" class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                </div>

                <!-- Field Permission Checklist -->
                <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-200 dark:border-slate-800">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">
                        <i class="fa-solid fa-list-check mr-1"></i> Field Permissions — which fields apply to this category?
                    </p>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2">
                        <?php foreach ($fieldOptions as $key => $opt): ?>
                            <label class="flex items-center gap-2 px-3 py-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 cursor-pointer hover:border-brand-medium transition text-sm">
                                <input type="checkbox" name="field_config[<?= $key ?>]" value="1"
                                       class="rounded text-brand-medium focus:ring-brand-medium"
                                       <?= ($key !== 'plate_number') ? 'checked' : '' ?>>
                                <i class="fa-solid <?= $opt['icon'] ?> text-brand-medium"></i>
                                <span class="text-slate-700 dark:text-slate-300"><?= $opt['label'] ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
                        <i class="fa-solid fa-circle-info mr-1"></i>
                        Example: tick <strong>License Plate</strong> only for the "Vehicles" category. Unticked fields will be hidden when adding/editing/viewing assets of that category.
                    </p>
                </div>

                <button type="submit" name="add" class="px-5 py-2 bg-brand-dark hover:bg-[#0e4f62] text-white font-bold text-sm rounded-lg transition shadow-sm">
                    <i class="fa-solid fa-plus mr-1"></i> Add Category
                </button>
            </form>
        </div>

        <!-- Categories table -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3 text-left">#</th>
                            <th class="px-6 py-3 text-left">Category</th>
                            <th class="px-6 py-3 text-left">Field Permissions</th>
                            <th class="px-6 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php foreach ($categories as $c): ?>
                            <?php $cfg = $c['field_config_parsed']; ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition align-top">
                                <td class="px-6 py-3 font-mono text-slate-600 dark:text-slate-400"><?= $c['id'] ?></td>
                                <td class="px-6 py-3">
                                    <div class="font-medium text-slate-700 dark:text-slate-300">
                                        <i class="<?= htmlspecialchars($normalizeAssetIcon($c['icon'] ?? 'fa-cube')) ?> text-brand-medium mr-1"></i>
                                        <?= htmlspecialchars($c['name']) ?>
                                    </div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5"><?= htmlspecialchars((string)$c['description']) ?></div>
                                </td>
                                <td class="px-6 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        <?php foreach ($fieldOptions as $key => $opt): ?>
                                            <?php if (!empty($cfg[$key])): ?>
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-bold rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                                    <i class="fa-solid <?= $opt['icon'] ?>"></i> <?= $opt['label'] ?>
                                                </span>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                        <?php if (!array_filter($cfg)): ?>
                                            <span class="text-xs text-slate-400 italic">None</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-3">
                                    <details class="group">
                                        <summary class="cursor-pointer text-xs font-bold text-brand-medium hover:text-brand-dark select-none">
                                            <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                        </summary>
                                        <form method="post" class="mt-3 space-y-2 p-3 border border-slate-200 dark:border-slate-700 rounded-lg bg-slate-50 dark:bg-slate-800/50">
                                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                                <input type="text" name="name" value="<?= htmlspecialchars($c['name']) ?>" class="px-2 py-1 text-xs border border-slate-300 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
                                                <input type="text" name="description" value="<?= htmlspecialchars((string)$c['description']) ?>" class="px-2 py-1 text-xs border border-slate-300 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
                                                <input type="text" name="icon" value="<?= htmlspecialchars($normalizeAssetIcon($c['icon'] ?? 'fa-cube')) ?>" class="px-2 py-1 text-xs border border-slate-300 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
                                            </div>
                                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-1">
                                                <?php foreach ($fieldOptions as $key => $opt): ?>
                                                    <label class="flex items-center gap-1 px-2 py-1 rounded bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs cursor-pointer">
                                                        <input type="checkbox" name="field_config[<?= $key ?>]" value="1"
                                                               class="rounded text-brand-medium focus:ring-brand-medium"
                                                               <?= !empty($cfg[$key]) ? 'checked' : '' ?>>
                                                        <i class="fa-solid <?= $opt['icon'] ?> text-brand-medium"></i>
                                                        <span class="text-slate-700 dark:text-slate-300"><?= $opt['label'] ?></span>
                                                    </label>
                                                <?php endforeach; ?>
                                            </div>
                                            <button type="submit" name="update" class="px-3 py-1 bg-brand-medium text-white text-xs font-bold rounded hover:bg-brand-dark transition">
                                                <i class="fa-solid fa-save mr-1"></i> Save Changes
                                            </button>
                                        </form>
                                    </details>
                                    <form method="post" onsubmit="return confirm('Delete this category?')" class="inline mt-2">
                                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <button type="submit" name="delete" class="text-xs text-rose-500 hover:text-rose-700 font-bold mt-1">
                                            <i class="fa-solid fa-trash"></i> Delete
                                        </button>
                                    </form>
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
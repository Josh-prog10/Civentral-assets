<?php
declare(strict_types=1);

$basePath = '../../';
require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/LguService.php';

use App\Service\LguService;

$lguService = new LguService($pdo);

if (!$isSuperAdmin && !$hasResourceAccess(['lgu', 'facility', 'facilities', 'facility_manage', 'facility management', 'facility reservation'])) {
    header('Location: ' . $basePath . 'pages/dashboard.php?error=access_denied');
    exit;
}

$message = $error = '';
if (empty($_SESSION['lgu_csrf_token'])) {
    $_SESSION['lgu_csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['lgu_csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($csrfToken, $_POST['csrf_token'])) {
        $error = 'Invalid request.';
    } else {
        if (isset($_POST['add'])) {
            $name = trim($_POST['name'] ?? '');
            $type = $_POST['type'] ?? 'other';
            $desc = trim($_POST['description'] ?? '');
            $capacity = (int)($_POST['capacity'] ?? 0);
            $location = trim($_POST['location'] ?? '');
            $status = $_POST['status'] ?? 'active';
            if ($name === '' || $location === '') {
                $error = 'Name and location are required.';
            } else {
                try {
                    if ($lguService->addFacility($name, $type, $desc, $capacity, $location, $status)) {
                        $message = 'Facility added.';
                    } else {
                        $error = 'Add failed.';
                    }
                } catch (Exception $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        } elseif (isset($_POST['update'])) {
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $type = $_POST['type'] ?? 'other';
            $desc = trim($_POST['description'] ?? '');
            $capacity = (int)($_POST['capacity'] ?? 0);
            $location = trim($_POST['location'] ?? '');
            $status = $_POST['status'] ?? 'active';
            if ($id < 1 || $name === '' || $location === '') {
                $error = 'Valid ID, name and location required.';
            } else {
                try {
                    if ($lguService->updateFacility($id, $name, $type, $desc, $capacity, $location, $status)) {
                        $message = 'Facility updated.';
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
                $error = 'Invalid facility ID.';
            } else {
                try {
                    if ($lguService->deleteFacility($id)) {
                        $message = 'Facility deleted.';
                    } else {
                        $error = 'Cannot delete facility with existing reservations.';
                    }
                } catch (Exception $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        }
    }
}

$facilities = $lguService->getFacilities();
$pageTitle = 'LGU Facilities';
include $basePath . 'includes/header.php';
include $basePath . 'includes/sidebar.php';
?>

<div class="flex-1 p-6 lg:p-8">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">
                <i class="fa-solid fa-warehouse text-brand-medium mr-3"></i>Facilities
            </h1>
            <a href="<?= $basePath ?>pages/lgu-facilities/ldb.php" class="text-sm font-bold text-brand-medium hover:text-brand-dark dark:hover:text-brand-light transition">
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
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-4">Add New Facility</h2>
            <form method="post" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="text" name="name" placeholder="Facility Name" required class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                <select name="type" class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                    <option value="covered_court">Covered Court</option>
                    <option value="function_hall">Function Hall</option>
                    <option value="barangay_hall">Barangay Hall</option>
                    <option value="conference_room">Conference Room</option>
                    <option value="auditorium">Auditorium</option>
                    <option value="other">Other</option>
                </select>
                <input type="text" name="description" placeholder="Description" class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                <input type="number" name="capacity" placeholder="Capacity" min="0" class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                <input type="text" name="location" placeholder="Location" class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                <div class="flex gap-2">
                    <select name="status" class="flex-1 px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <button type="submit" name="add" class="px-4 py-2 bg-brand-dark hover:bg-[#0e4f62] text-white font-bold text-sm rounded-lg transition shadow-sm whitespace-nowrap">
                        <i class="fa-solid fa-plus mr-1"></i> Add
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3 text-left">#</th>
                            <th class="px-6 py-3 text-left">Name</th>
                            <th class="px-6 py-3 text-left">Type</th>
                            <th class="px-6 py-3 text-left">Capacity</th>
                            <th class="px-6 py-3 text-left">Location</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php foreach ($facilities as $f): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                <td class="px-6 py-3 font-mono text-slate-600 dark:text-slate-400"><?= $f['id'] ?></td>
                                <td class="px-6 py-3 font-medium text-slate-700 dark:text-slate-300"><?= htmlspecialchars($f['name']) ?></td>
                                <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= str_replace('_', ' ', htmlspecialchars($f['type'])) ?></td>
                                <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= $f['capacity'] ?></td>
                                <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= htmlspecialchars($f['location']) ?></td>
                                <td class="px-6 py-3">
                                    <span class="inline-block px-2 py-0.5 text-xs font-bold rounded-full <?= $f['status'] == 'active' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' ?>">
                                        <?= ucfirst($f['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        <form method="post" class="inline-flex items-center gap-1 flex-wrap">
                                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                            <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                            <input type="text" name="name" value="<?= htmlspecialchars($f['name']) ?>" class="w-16 px-1 py-0.5 text-xs border border-slate-300 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
                                            <select name="type" class="px-1 py-0.5 text-xs border border-slate-300 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
                                                <?php foreach (['covered_court','function_hall','barangay_hall','conference_room','auditorium','other'] as $t): ?>
                                                    <option value="<?= $t ?>" <?= $f['type']==$t?'selected':'' ?>><?= str_replace('_',' ',$t) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="text" name="description" value="<?= htmlspecialchars($f['description']) ?>" class="w-20 px-1 py-0.5 text-xs border border-slate-300 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
                                            <input type="number" name="capacity" value="<?= $f['capacity'] ?>" class="w-12 px-1 py-0.5 text-xs border border-slate-300 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
                                            <input type="text" name="location" value="<?= htmlspecialchars($f['location']) ?>" class="w-20 px-1 py-0.5 text-xs border border-slate-300 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
                                            <select name="status" class="px-1 py-0.5 text-xs border border-slate-300 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
                                                <option value="active" <?= $f['status']=='active'?'selected':'' ?>>Active</option>
                                                <option value="inactive" <?= $f['status']=='inactive'?'selected':'' ?>>Inactive</option>
                                            </select>
                                            <button type="submit" name="update" class="px-2 py-0.5 bg-brand-medium text-white text-xs rounded hover:bg-brand-dark transition">Update</button>
                                        </form>
                                        <form method="post" onsubmit="return confirm('Delete this facility?')" class="inline">
                                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                            <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                            <button type="submit" name="delete" class="px-2 py-0.5 bg-rose-500 text-white text-xs rounded hover:bg-rose-600 transition">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($facilities)): ?>
                            <tr><td colspan="7" class="px-6 py-8 text-center text-slate-400 dark:text-slate-500">No facilities.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include $basePath . 'includes/footer.php'; ?>
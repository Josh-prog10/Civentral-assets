<?php
declare(strict_types=1);

$basePath = '../../';
require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/CemeteryService.php';

use App\Service\CemeteryService;

$cemeteryService = new CemeteryService($pdo);

if (!$isSuperAdmin && !$hasResourceAccess(['cemetery', 'deceased'])) {
    header('Location: ' . $basePath . 'pages/dashboard.php?error=access_denied');
    exit;
}

$message = $error = '';
if (empty($_SESSION['cemetery_csrf_token'])) {
    $_SESSION['cemetery_csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['cemetery_csrf_token'];

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($csrfToken, $_POST['csrf_token'])) {
        $error = 'Invalid request.';
    } else {
        // (existing add/update/delete code unchanged)
        if (isset($_POST['add'])) {
            $fn = trim($_POST['first_name'] ?? '');
            $ln = trim($_POST['last_name'] ?? '');
            $dob = $_POST['date_of_birth'] ?: null;
            $dod = $_POST['date_of_death'] ?: null;
            $notes = trim($_POST['notes'] ?? '');
            if ($fn === '' || $ln === '' || $dod === null) {
                $error = 'First name, last name, and date of death are required.';
            } else {
                try {
                    if ($cemeteryService->addDeceased($fn, $ln, $dob, $dod, $notes)) {
                        $message = 'Deceased record added.';
                    } else {
                        $error = 'Add failed.';
                    }
                } catch (Exception $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        } elseif (isset($_POST['update'])) {
            $id = (int)($_POST['id'] ?? 0);
            $fn = trim($_POST['first_name'] ?? '');
            $ln = trim($_POST['last_name'] ?? '');
            $dob = $_POST['date_of_birth'] ?: null;
            $dod = $_POST['date_of_death'] ?: null;
            $notes = trim($_POST['notes'] ?? '');
            if ($id < 1 || $fn === '' || $ln === '' || $dod === null) {
                $error = 'Valid ID, name, and date of death are required.';
            } else {
                try {
                    if ($cemeteryService->updateDeceased($id, $fn, $ln, $dob, $dod, $notes)) {
                        $message = 'Record updated.';
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
                $error = 'Invalid ID.';
            } else {
                try {
                    if ($cemeteryService->deleteDeceased($id)) {
                        $message = 'Deleted.';
                    } else {
                        $error = 'Cannot delete a deceased person with an existing burial.';
                    }
                } catch (Exception $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        }
    }
}

// ---------- SEARCH ----------
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$deceased = $cemeteryService->getDeceased($search);
// -----------------------------

$pageTitle = 'Deceased Records';
include $basePath . 'includes/header.php';
include $basePath . 'includes/sidebar.php';
?>

<div class="flex-1 p-6 lg:p-8">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">
                <i class="fa-solid fa-user-injured text-brand-medium mr-3"></i>Deceased Information
            </h1>
            <a href="<?= $basePath ?>pages/cemetery/cdb.php" class="text-sm font-bold text-brand-medium hover:text-brand-dark dark:hover:text-brand-light transition">
                <i class="fa-solid fa-arrow-left mr-1"></i> Back to Dashboard
            </a>
        </div>

        <?php if ($message): ?>
            <div class="mb-4 p-3 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 rounded-lg text-sm font-bold"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 rounded-lg text-sm font-bold"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Add Form (unchanged) -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm mb-8">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-4">Add Deceased</h2>
            <form method="post" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="text" name="first_name" placeholder="First Name" required class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                <input type="text" name="last_name" placeholder="Last Name" required class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                <input type="date" name="date_of_birth" min="1800-01-01" class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                <input type="date" name="date_of_death" min="1800-01-01" required class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                <div class="flex gap-2">
                    <input type="text" name="notes" placeholder="Notes" class="flex-1 px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                    <button type="submit" name="add" class="px-4 py-2 bg-brand-dark hover:bg-[#0e4f62] text-white font-bold text-sm rounded-lg transition shadow-sm whitespace-nowrap">
                        <i class="fa-solid fa-plus mr-1"></i> Add
                    </button>
                </div>
            </form>
        </div>

        <!-- Search Bar -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm mb-6">
            <form method="get" class="flex items-center gap-4">
                <input type="text" name="search" placeholder="Search by name..." value="<?= htmlspecialchars($search) ?>" 
                       class="flex-1 px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                <button type="submit" class="px-4 py-2 bg-brand-medium hover:bg-brand-dark text-white font-bold text-sm rounded-lg transition shadow-sm">
                    <i class="fa-solid fa-search mr-1"></i> Search
                </button>
                <?php if ($search): ?>
                    <a href="<?= $_SERVER['PHP_SELF'] ?>" class="px-4 py-2 bg-slate-300 dark:bg-slate-700 text-slate-800 dark:text-white font-bold text-sm rounded-lg hover:bg-slate-400 dark:hover:bg-slate-600 transition">
                        <i class="fa-solid fa-times mr-1"></i> Clear
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Table (unchanged except added search row count) -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3 text-left">#</th>
                            <th class="px-6 py-3 text-left">First Name</th>
                            <th class="px-6 py-3 text-left">Last Name</th>
                            <th class="px-6 py-3 text-left">DOB</th>
                            <th class="px-6 py-3 text-left">DOD</th>
                            <th class="px-6 py-3 text-left">Cemetery</th>
                            <th class="px-6 py-3 text-left">Notes</th>
                            <th class="px-6 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php foreach ($deceased as $d): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                <td class="px-6 py-3 font-mono text-slate-600 dark:text-slate-400"><?= $d['id'] ?></td>
                                <td class="px-6 py-3 font-medium text-slate-700 dark:text-slate-300"><?= htmlspecialchars($d['first_name']) ?></td>
                                <td class="px-6 py-3 font-medium text-slate-700 dark:text-slate-300"><?= htmlspecialchars($d['last_name']) ?></td>
                                <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= htmlspecialchars($d['date_of_birth']) ?></td>
                                <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= htmlspecialchars($d['date_of_death']) ?></td>
                                <td class="px-6 py-3 text-slate-600 dark:text-slate-400">
                                    <?= htmlspecialchars($d['cemetery_name'] ?? 'Not buried') ?>
                                </td>
                                <td class="px-6 py-3 text-slate-500 dark:text-slate-400 text-xs"><?= htmlspecialchars($d['notes']) ?></td>
                                <td class="px-6 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        <form method="post" class="inline-flex items-center gap-1 flex-wrap">
                                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                            <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                            <input type="text" name="first_name" value="<?= htmlspecialchars($d['first_name']) ?>" class="w-16 px-1 py-0.5 text-xs border border-slate-300 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
                                            <input type="text" name="last_name" value="<?= htmlspecialchars($d['last_name']) ?>" class="w-16 px-1 py-0.5 text-xs border border-slate-300 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
                                            <input type="date" name="date_of_birth" value="<?= htmlspecialchars($d['date_of_birth']) ?>" class="w-20 px-1 py-0.5 text-xs border border-slate-300 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
                                            <input type="date" name="date_of_death" value="<?= htmlspecialchars($d['date_of_death']) ?>" class="w-20 px-1 py-0.5 text-xs border border-slate-300 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
                                            <input type="text" name="notes" value="<?= htmlspecialchars($d['notes']) ?>" class="w-16 px-1 py-0.5 text-xs border border-slate-300 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
                                            <button type="submit" name="update" class="px-2 py-0.5 bg-brand-medium text-white text-xs rounded hover:bg-brand-dark transition">Update</button>
                                        </form>
                                        <form method="post" onsubmit="return confirm('Delete this record?')" class="inline">
                                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                            <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                            <button type="submit" name="delete" class="px-2 py-0.5 bg-rose-500 text-white text-xs rounded hover:bg-rose-600 transition">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($deceased)): ?>
                            <tr><td colspan="8" class="px-6 py-8 text-center text-slate-400 dark:text-slate-500">
                                <?= $search ? 'No results found for "' . htmlspecialchars($search) . '".' : 'No deceased records.' ?>
                            </td></tr>
                        <?php else: ?>
                            <tr><td colspan="8" class="px-6 py-2 text-xs text-slate-400 dark:text-slate-500 border-t border-slate-200 dark:border-slate-700">
                                Showing <?= count($deceased) ?> record(s)
                                <?php if ($search): ?> (filtered by search)<?php endif; ?>
                            </td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include $basePath . 'includes/footer.php'; ?>
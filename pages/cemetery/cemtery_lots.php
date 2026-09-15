<?php
declare(strict_types=1);

$basePath = '../../';
require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/CemeteryService.php';

use App\Service\CemeteryService;

$cemeteryService = new CemeteryService($pdo);

if (!$isSuperAdmin && !$hasResourceAccess(['cemetery', 'lot'])) {
    header('Location: ' . $basePath . 'pages/dashboard.php?error=access_denied');
    exit;
}

$message = $error = '';
if (empty($_SESSION['cemetery_csrf_token'])) {
    $_SESSION['cemetery_csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['cemetery_csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($csrfToken, $_POST['csrf_token'])) {
        $error = 'Invalid request.';
    } else {
        if (isset($_POST['add'])) {
            $section = trim($_POST['section'] ?? '');
            $row = trim($_POST['row'] ?? '');
            $number = trim($_POST['number'] ?? '');
            $status = $_POST['status'] ?? 'available';
            $notes = trim($_POST['notes'] ?? '');
            $cemeteryId = (int)($_POST['cemetery_id'] ?? 0);
            if ($section === '' || $row === '' || $number === '' || $cemeteryId < 1) {
                $error = 'Section, Row, Lot Number, and Cemetery are required.';
            } else {
                try {
                    if ($cemeteryService->addLot($section, $row, $number, $status, $notes, $cemeteryId)) {
                        $message = 'Lot added successfully.';
                    } else {
                        $error = 'Failed to add lot.';
                    }
                } catch (Exception $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        } elseif (isset($_POST['update'])) {
            $id = (int)($_POST['id'] ?? 0);
            $section = trim($_POST['section'] ?? '');
            $row = trim($_POST['row'] ?? '');
            $number = trim($_POST['number'] ?? '');
            $status = $_POST['status'] ?? 'available';
            $notes = trim($_POST['notes'] ?? '');
            $cemeteryId = (int)($_POST['cemetery_id'] ?? 0);
            if ($id < 1 || $section === '' || $row === '' || $number === '' || $cemeteryId < 1) {
                $error = 'Valid ID and all fields are required.';
            } else {
                try {
                    if ($cemeteryService->updateLot($id, $section, $row, $number, $status, $notes, $cemeteryId)) {
                        $message = 'Lot updated.';
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
                $error = 'Invalid lot ID.';
            } else {
                try {
                    if ($cemeteryService->deleteLot($id)) {
                        $message = 'Lot deleted.';
                    } else {
                        $error = 'Cannot delete a lot that is occupied.';
                    }
                } catch (Exception $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        }
    }
}

$cemeteries = $cemeteryService->getCemeteries();
$lots = $cemeteryService->getLots(); // now includes cemetery_name

$pageTitle = 'Cemetery Lots';
include $basePath . 'includes/header.php';
include $basePath . 'includes/sidebar.php';
?>

<div class="flex-1 p-6 lg:p-8">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">
                <i class="fa-solid fa-vector-square text-brand-medium mr-3"></i>Cemetery Lots
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

        <!-- Add Lot Form -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm mb-8">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-4">Add New Lot</h2>
            <form method="post" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <select name="cemetery_id" required class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                    <option value="">Select Cemetery</option>
                    <?php foreach ($cemeteries as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="section" placeholder="Section" required class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                <input type="text" name="row" placeholder="Row" required class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                <input type="text" name="number" placeholder="Lot Number" required class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                <select name="status" class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                    <option value="available">Available</option>
                    <option value="reserved">Reserved</option>
                    <option value="occupied">Occupied</option>
                </select>
                <div class="flex gap-2">
                    <input type="text" name="notes" placeholder="Notes" class="flex-1 px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                    <button type="submit" name="add" class="px-4 py-2 bg-brand-dark hover:bg-[#0e4f62] text-white font-bold text-sm rounded-lg transition shadow-sm whitespace-nowrap">
                        <i class="fa-solid fa-plus mr-1"></i> Add
                    </button>
                </div>
            </form>
        </div>

        <!-- Lots Table -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3 text-left">#</th>
                            <th class="px-6 py-3 text-left">Cemetery</th>
                            <th class="px-6 py-3 text-left">Section</th>
                            <th class="px-6 py-3 text-left">Row</th>
                            <th class="px-6 py-3 text-left">Lot#</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-left">Notes</th>
                            <th class="px-6 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php foreach ($lots as $lot): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                <td class="px-6 py-3 font-mono text-slate-600 dark:text-slate-400"><?= $lot['id'] ?></td>
                                <td class="px-6 py-3 text-slate-700 dark:text-slate-300"><?= htmlspecialchars($lot['cemetery_name']) ?></td>
                                <td class="px-6 py-3 font-medium text-slate-700 dark:text-slate-300"><?= htmlspecialchars($lot['section']) ?></td>
                                <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= htmlspecialchars($lot['row_num']) ?></td>
                                <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= htmlspecialchars($lot['lot_number']) ?></td>
                                <td class="px-6 py-3">
                                    <span class="inline-block px-2 py-0.5 text-xs font-bold rounded-full
                                        <?php
                                        $statusClass = match ($lot['status']) {
                                            'available' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                                            'reserved'  => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                                            'occupied'  => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300',
                                            default     => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
                                        };
                                        echo $statusClass;
                                        ?>">
                                        <?= ucfirst($lot['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-slate-500 dark:text-slate-400 text-xs"><?= htmlspecialchars($lot['notes']) ?></td>
                                <td class="px-6 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        <form method="post" class="inline-flex items-center gap-1 flex-wrap">
                                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                            <input type="hidden" name="id" value="<?= $lot['id'] ?>">
                                            <select name="cemetery_id" class="px-1 py-0.5 text-xs border border-slate-300 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
                                                <?php foreach ($cemeteries as $c): ?>
                                                    <option value="<?= $c['id'] ?>" <?= $c['id']==$lot['cemetery_id']?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="text" name="section" value="<?= htmlspecialchars($lot['section']) ?>" class="w-12 px-1 py-0.5 text-xs border border-slate-300 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
                                            <input type="text" name="row" value="<?= htmlspecialchars($lot['row_num']) ?>" class="w-10 px-1 py-0.5 text-xs border border-slate-300 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
                                            <input type="text" name="number" value="<?= htmlspecialchars($lot['lot_number']) ?>" class="w-10 px-1 py-0.5 text-xs border border-slate-300 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
                                            <select name="status" class="px-1 py-0.5 text-xs border border-slate-300 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
                                                <option value="available" <?= $lot['status']=='available'?'selected':'' ?>>Available</option>
                                                <option value="reserved" <?= $lot['status']=='reserved'?'selected':'' ?>>Reserved</option>
                                                <option value="occupied" <?= $lot['status']=='occupied'?'selected':'' ?>>Occupied</option>
                                            </select>
                                            <input type="text" name="notes" value="<?= htmlspecialchars($lot['notes']) ?>" class="w-16 px-1 py-0.5 text-xs border border-slate-300 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
                                            <button type="submit" name="update" class="px-2 py-0.5 bg-brand-medium text-white text-xs rounded hover:bg-brand-dark transition">Update</button>
                                        </form>
                                        <form method="post" onsubmit="return confirm('Delete this lot?')" class="inline">
                                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                            <input type="hidden" name="id" value="<?= $lot['id'] ?>">
                                            <button type="submit" name="delete" class="px-2 py-0.5 bg-rose-500 text-white text-xs rounded hover:bg-rose-600 transition">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($lots)): ?>
                            <tr><td colspan="8" class="px-6 py-8 text-center text-slate-400 dark:text-slate-500">No lots found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include $basePath . 'includes/footer.php'; ?>
<?php
declare(strict_types=1);

$basePath = '../../';
require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/CemeteryService.php';

use App\Service\CemeteryService;

$cemeteryService = new CemeteryService($pdo);

if (!$isSuperAdmin && !$hasResourceAccess(['cemetery', 'burial'])) {
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
            $lotId = (int)($_POST['lot_id'] ?? 0);
            $deceasedId = (int)($_POST['deceased_id'] ?? 0);
            $burialDate = $_POST['burial_date'] ?? '';
            $notes = trim($_POST['notes'] ?? '');
            if ($lotId < 1 || $deceasedId < 1 || $burialDate === '') {
                $error = 'Lot, Deceased, and Burial Date are required.';
            } elseif ($cemeteryService->isDeceasedBuried($deceasedId)) {
                // Server-side guard: block already-buried deceased
                $error = 'This deceased person already has a burial record.';
            } else {
                try {
                    if ($cemeteryService->addBurial($lotId, $deceasedId, $burialDate, $notes)) {
                        $message = 'Burial recorded.';
                    } else {
                        $error = 'Burial failed. Lot may be already occupied or invalid data.';
                    }
                } catch (Exception $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        } elseif (isset($_POST['update'])) {
            $id = (int)($_POST['id'] ?? 0);
            $lotId = (int)($_POST['lot_id'] ?? 0);
            $deceasedId = (int)($_POST['deceased_id'] ?? 0);
            $burialDate = $_POST['burial_date'] ?? '';
            $notes = trim($_POST['notes'] ?? '');
            if ($id < 1 || $lotId < 1 || $deceasedId < 1 || $burialDate === '') {
                $error = 'Valid ID, Lot, Deceased, and Burial Date are required.';
            } elseif ($cemeteryService->isDeceasedBuried($deceasedId, $id)) {
                // Server-side guard: block switching to a deceased who is buried elsewhere
                $error = 'The selected deceased person already has a burial record.';
            } else {
                try {
                    if ($cemeteryService->updateBurial($id, $lotId, $deceasedId, $burialDate, $notes)) {
                        $message = 'Burial updated.';
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
                $error = 'Invalid burial ID.';
            } else {
                try {
                    if ($cemeteryService->deleteBurial($id)) {
                        $message = 'Burial deleted and lot released.';
                    } else {
                        $error = 'Delete failed.';
                    }
                } catch (Exception $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        }
    }
}

// getBurials() now includes cemetery_name via the join
$burials = $cemeteryService->getBurials();

// Available lots – now with cemetery_name
$availableLots = $cemeteryService->getLots('available');

// All lots for update dropdown – now with cemetery_name
$allLots = $cemeteryService->getLots();

// For the "Add Burial" form: only deceased who are NOT yet buried
$deceasedList = $cemeteryService->getUnburiedDeceased();

// For the "Update" dropdowns: full list (we filter per-row in the template)
// getDeceased() includes burial_id, so we can tell who is already buried.
$allDeceased = $cemeteryService->getDeceased();

$pageTitle = 'Burial Records';
include $basePath . 'includes/header.php';
include $basePath . 'includes/sidebar.php';
?>

<div class="flex-1 p-6 lg:p-8">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">
                <i class="fa-solid fa-cross text-brand-medium mr-3"></i>Burial Management
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

        <!-- Add Burial Form -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm mb-8">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-4">Record New Burial</h2>
            <form method="post" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <select name="lot_id" required class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                    <option value="">Select Available Lot</option>
                    <?php foreach ($availableLots as $lot): ?>
                        <option value="<?= $lot['id'] ?>">
                            <?= htmlspecialchars($lot['cemetery_name'] . ' - ' . $lot['section'] . '-' . $lot['row_num'] . '-' . $lot['lot_number']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="deceased_id" required class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                    <option value="">Select Deceased</option>
                    <?php foreach ($deceasedList as $d): ?>
                        <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['first_name'].' '.$d['last_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="date" name="burial_date" required class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                <div class="flex gap-2 col-span-2">
                    <input type="text" name="notes" placeholder="Notes" class="flex-1 px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                    <button type="submit" name="add" class="px-4 py-2 bg-brand-dark hover:bg-[#0e4f62] text-white font-bold text-sm rounded-lg transition shadow-sm whitespace-nowrap">
                        <i class="fa-solid fa-plus mr-1"></i> Record
                    </button>
                </div>
            </form>
            <?php if (empty($deceasedList)): ?>
                <p class="mt-3 text-xs text-amber-600 dark:text-amber-400 font-bold">
                    <i class="fa-solid fa-circle-info mr-1"></i>
                    All deceased records already have a burial. Add a new deceased record to record another burial.
                </p>
            <?php endif; ?>
        </div>

        <!-- Burials Table -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3 text-left">#</th>
                            <th class="px-6 py-3 text-left">Deceased</th>
                            <th class="px-6 py-3 text-left">Cemetery</th>
                            <th class="px-6 py-3 text-left">Lot</th>
                            <th class="px-6 py-3 text-left">Burial Date</th>
                            <th class="px-6 py-3 text-left">Notes</th>
                            <th class="px-6 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php foreach ($burials as $b): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                <td class="px-6 py-3 font-mono text-slate-600 dark:text-slate-400"><?= $b['id'] ?></td>
                                <td class="px-6 py-3 font-medium text-slate-700 dark:text-slate-300"><?= htmlspecialchars($b['first_name'].' '.$b['last_name']) ?></td>
                                <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= htmlspecialchars($b['cemetery_name']) ?></td>
                                <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= htmlspecialchars($b['section'].'-'.$b['row_num'].'-'.$b['lot_number']) ?></td>
                                <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= htmlspecialchars($b['burial_date']) ?></td>
                                <td class="px-6 py-3 text-slate-500 dark:text-slate-400 text-xs"><?= htmlspecialchars($b['notes']) ?></td>
                                <td class="px-6 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        <form method="post" class="inline-flex items-center gap-1 flex-wrap">
                                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                            <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                            <select name="lot_id" class="px-1 py-0.5 text-xs border border-slate-300 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
                                                <?php foreach ($allLots as $lot): ?>
                                                    <option value="<?= $lot['id'] ?>" <?= $lot['id']==$b['lot_id']?'selected':'' ?>>
                                                        <?= htmlspecialchars($lot['cemetery_name'] . ' - ' . $lot['section'] . '-' . $lot['row_num'] . '-' . $lot['lot_number']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <select name="deceased_id" class="px-1 py-0.5 text-xs border border-slate-300 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
                                                <?php foreach ($allDeceased as $d): ?>
                                                    <?php
                                                        // Skip deceased who are buried by a DIFFERENT burial record.
                                                        // Keep the one currently assigned to this row (so it stays selectable).
                                                        $buriedElsewhere = !empty($d['burial_id']) && (int)$d['burial_id'] !== (int)$b['id'];
                                                        if ($buriedElsewhere) {
                                                            continue;
                                                        }
                                                    ?>
                                                    <option value="<?= $d['id'] ?>" <?= $d['id']==$b['deceased_id']?'selected':'' ?>>
                                                        <?= htmlspecialchars($d['first_name'].' '.$d['last_name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="date" name="burial_date" value="<?= htmlspecialchars($b['burial_date']) ?>" class="w-24 px-1 py-0.5 text-xs border border-slate-300 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
                                            <input type="text" name="notes" value="<?= htmlspecialchars($b['notes']) ?>" class="w-16 px-1 py-0.5 text-xs border border-slate-300 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
                                            <button type="submit" name="update" class="px-2 py-0.5 bg-brand-medium text-white text-xs rounded hover:bg-brand-dark transition">Update</button>
                                        </form>
                                        <form method="post" onsubmit="return confirm('Delete this burial?')" class="inline">
                                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                            <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                            <button type="submit" name="delete" class="px-2 py-0.5 bg-rose-500 text-white text-xs rounded hover:bg-rose-600 transition">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($burials)): ?>
                            <tr><td colspan="7" class="px-6 py-8 text-center text-slate-400 dark:text-slate-500">No burials recorded.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include $basePath . 'includes/footer.php'; ?>
<?php
declare(strict_types=1);

$basePath = '../../';
require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/ParkService.php';

use App\Service\ParkService;

$parkService = new ParkService($pdo);


if (!$isSuperAdmin && !$hasResourceAccess(['parks', 'availability'])) {
    header('Location: ' . $basePath . 'pages/dashboard.php?error=access_denied');
    exit;
}

$facilities = $parkService->getFacilities(true);
$selectedFacility = isset($_GET['facility_id']) ? (int)$_GET['facility_id'] : null;
$selectedDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

$bookings = [];
if ($selectedFacility) {
    $bookings = $parkService->getAvailability($selectedFacility, $selectedDate);
}

$pageTitle = 'Availability View';
include $basePath . 'includes/header.php';
include $basePath . 'includes/sidebar.php';
?>

<div class="flex-1 p-6 lg:p-8">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight mb-6">
            <i class="fa-solid fa-eye text-brand-medium mr-3"></i>Facility Availability
        </h1>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm mb-8">
            <form method="get" class="flex flex-wrap gap-4 items-end">
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Facility</label>
                    <select name="facility_id" class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                        <option value="">Select a facility</option>
                        <?php foreach ($facilities as $f): ?>
                            <option value="<?= $f['id'] ?>" <?= ($selectedFacility == $f['id']) ? 'selected' : '' ?>><?= htmlspecialchars($f['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Date</label>
                    <input type="date" name="date" value="<?= htmlspecialchars($selectedDate) ?>" class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                </div>
                <button type="submit" class="px-4 py-2 bg-brand-dark hover:bg-[#0e4f62] text-white font-bold text-sm rounded-lg transition shadow-sm">
                    Check Availability
                </button>
                <a href="<?= $basePath ?>pages/parks/avail.php" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-sm rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition">Reset</a>
            </form>
        </div>

        <?php if ($selectedFacility): ?>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800">
                    <h2 class="font-bold text-slate-800 dark:text-white">
                        Bookings for <?= htmlspecialchars($parkService->getFacility($selectedFacility)['name'] ?? '') ?> on <?= date('F d, Y', strtotime($selectedDate)) ?>
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <?php if ($bookings): ?>
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">
                                <tr>
                                    <th class="px-6 py-3 text-left">Event</th>
                                    <th class="px-6 py-3 text-left">Reserve To</th>
                                    <th class="px-6 py-3 text-left">Start</th>
                                    <th class="px-6 py-3 text-left">End</th>
                                    <th class="px-6 py-3 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <?php foreach ($bookings as $b): ?>
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                        <td class="px-6 py-3 font-medium text-slate-700 dark:text-slate-300"><?= htmlspecialchars($b['event_name']) ?></td>
                                        <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= htmlspecialchars((string)$b['user_name']) ?></td>
                                        <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= date('H:i', strtotime($b['start_datetime'])) ?></td>
                                        <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= date('H:i', strtotime($b['end_datetime'])) ?></td>
                                        <td class="px-6 py-3">
                                            <?php
                                            $isExpired  = ($b['status'] === 'cancelled' && ($b['cancelled_reason'] ?? '') === 'expired');
                                            $statusText = $isExpired ? 'Auto-cancelled' : ucfirst($b['status']);
                                            $statusClass = match ($b['status']) {
                                                'pending'   => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                                                'approved'  => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                                                'cancelled' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400',
                                                'rejected'  => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300',
                                                default     => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
                                            };
                                            ?>
                                            <span class="inline-block px-2 py-0.5 text-xs font-bold rounded-full <?= $statusClass ?>">
                                                <?= $statusText ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="p-8 text-center text-slate-400 dark:text-slate-500">
                            <i class="fa-solid fa-calendar-day text-2xl mb-2 block"></i>
                            <p>No bookings found for this facility on this date.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-8 text-center text-slate-400 dark:text-slate-500">
                <i class="fa-solid fa-magnifying-glass text-2xl mb-2 block"></i>
                <p>Select a facility and date to view availability.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include $basePath . 'includes/footer.php'; ?>
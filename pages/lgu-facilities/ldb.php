<?php
declare(strict_types=1);

$basePath = '../../';

require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/LguService.php';

use App\Service\LguService;

$lguService = new LguService($pdo);

if (!$isSuperAdmin && !$hasResourceAccess(['lgu', 'facility'])) {
    header('Location: ' . $basePath . 'pages/dashboard.php?error=access_denied');
    exit;
}

$stats = $lguService->getStats();

$upcoming = $lguService->getReservations([
    'start_date_from' => date('Y-m-d 00:00:00'),
    'start_date_to'   => date('Y-m-d 23:59:59', strtotime('+7 days')),
    'status'          => 'approved'
]);

$pageTitle = 'LGU Facility Reservation Dashboard';
include $basePath . 'includes/header.php';
include $basePath . 'includes/sidebar.php';
?>

<div class="flex-1 p-6 lg:p-8">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight mb-6">
            <i class="fa-solid fa-building-columns text-brand-medium mr-3"></i>LGU Facility Reservation
        </h1>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Active Facilities</p>
                <p class="text-2xl font-black text-slate-800 dark:text-white mt-1"><?= $stats['active_facilities'] ?></p>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Today's Reservations</p>
                <p class="text-2xl font-black text-slate-800 dark:text-white mt-1"><?= $stats['today_reservations'] ?></p>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Pending Approvals</p>
                <p class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1"><?= $stats['pending_approvals'] ?></p>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Upcoming (7 days)</p>
                <p class="text-2xl font-black text-brand-dark dark:text-brand-medium mt-1"><?= $stats['upcoming_events'] ?></p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800">
                <h2 class="font-bold text-slate-800 dark:text-white">Upcoming Approved Reservations</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3 text-left">Purpose</th>
                            <th class="px-6 py-3 text-left">Facility</th>
                            <th class="px-6 py-3 text-left">Start</th>
                            <th class="px-6 py-3 text-left">End</th>
                            <th class="px-6 py-3 text-left">User</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php if ($upcoming): ?>
                            <?php foreach ($upcoming as $r): ?>
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                    <td class="px-6 py-3 font-medium text-slate-700 dark:text-slate-300"><?= htmlspecialchars($r['purpose']) ?></td>
                                    <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= htmlspecialchars($r['facility_name']) ?></td>
                                    <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= date('M d, Y H:i', strtotime($r['start_datetime'])) ?></td>
                                    <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= date('M d, Y H:i', strtotime($r['end_datetime'])) ?></td>
                                    <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= htmlspecialchars((string)$r['user_name']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="px-6 py-8 text-center text-slate-400 dark:text-slate-500">No upcoming approved reservations.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include $basePath . 'includes/footer.php'; ?>
<?php
declare(strict_types=1);

$basePath = '../../';
require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/ParkService.php';

use App\Service\ParkService;

$parkService = new ParkService($pdo);

if (!$isSuperAdmin && !$hasResourceAccess(['parks', 'reservation'])) {
    header('Location: ' . $basePath . 'pages/dashboard.php?error=access_denied');
    exit;
}

$message = $error = '';
if (empty($_SESSION['parks_csrf_token'])) {
    $_SESSION['parks_csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['parks_csrf_token'];
$facilities = $parkService->getFacilities(true);

// Get logged-in user ID (may be null if not logged in, but we expect it)
$userId = $headerUser['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($csrfToken, $_POST['csrf_token'])) {
        $error = 'Invalid request.';
    } else {
        $facilityId  = (int)($_POST['facility_id'] ?? 0);
        $userName    = trim($_POST['user_name'] ?? '');
        $eventName   = trim($_POST['event_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $start       = $_POST['start_datetime'] ?? '';
        $end         = $_POST['end_datetime'] ?? '';

        if ($facilityId < 1 || $userName === '' || $eventName === '' || $start === '' || $end === '') {
            $error = 'All required fields must be filled.';
        } elseif (strtotime($start) >= strtotime($end)) {
            $error = 'End time must be after start time.';
        } elseif (strtotime($start) < time()) {
            $error = 'Start time cannot be in the past.';
        } else {
            try {
                if ($parkService->addReservation($facilityId, $userId, $userName, $eventName, $description, $start, $end)) {
                    $message = 'Reservation submitted successfully! It is pending approval.';
                } else {
                    $error = 'Time slot conflict or invalid data. Please choose another time.';
                }
            } catch (Exception $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

$pageTitle = 'New Reservation';
include $basePath . 'includes/header.php';
include $basePath . 'includes/sidebar.php';
?>

<div class="flex-1 p-6 lg:p-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight mb-6">
            <i class="fa-solid fa-calendar-plus text-brand-medium mr-3"></i>Make a Reservation
        </h1>

        <?php if ($message): ?>
            <div class="mb-4 p-3 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 rounded-lg text-sm font-bold"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 rounded-lg text-sm font-bold"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm">
            <form method="post" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Facility</label>
                    <select name="facility_id" required class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                        <option value="">Select a facility</option>
                        <?php foreach ($facilities as $f): ?>
                            <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['name']) ?> (capacity <?= $f['capacity'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Your Full Name</label>
                    <input type="text" name="user_name" required class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none" placeholder="e.g., Juan Dela Cruz">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Event Name</label>
                    <input type="text" name="event_name" required class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Description (optional)</label>
                    <textarea name="description" rows="2" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Start Date & Time</label>
                    <input type="datetime-local" name="start_datetime" required class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">End Date & Time</label>
                    <input type="datetime-local" name="end_datetime" required class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="px-6 py-2 bg-brand-dark hover:bg-[#0e4f62] text-white font-bold text-sm rounded-lg transition shadow-sm">
                        <i class="fa-solid fa-check mr-1"></i> Submit Reservation
                    </button>
                    <a href="<?= $basePath ?>pages/parks/reservation.php" class="px-6 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-sm rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include $basePath . 'includes/footer.php'; ?>
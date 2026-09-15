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

$isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (isset($_SERVER['HTTP_ACCEPT'])
        && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

$id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['id'] ?? 0);
$reservation = $id > 0 ? $parkService->getReservation($id) : null;

if (!$reservation) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Reservation not found.']);
        exit;
    }
    header('Location: ' . $basePath . 'pages/parks/reservation.php?error=not_found');
    exit;
}

$message = $error = '';
if (empty($_SESSION['parks_csrf_token'])) {
    $_SESSION['parks_csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['parks_csrf_token'];

$methodLabels = [
    'cash'          => 'Cash',
    'gcash'         => 'GCash',
    'bank_transfer' => 'Bank Transfer',
    'check'         => 'Check',
    'other'         => 'Other',
];

$siteName = defined('SITE_NAME') ? SITE_NAME : 'Parks & Recreation Reservation System';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($csrfToken, $_POST['csrf_token'])) {
        $error = 'Invalid request.';
    } else {
        $amount    = (float)($_POST['amount'] ?? 0);
        $method    = $_POST['method'] ?? '';
        $reference = trim($_POST['reference'] ?? '');
        $notes     = trim($_POST['notes'] ?? '');

        $result = $parkService->recordPayment($id, $amount, $method, $reference, $notes ?: null);

        if ($result['success']) {
            if ($isAjax) {
                $receipt = $parkService->getReceipt($id);
                $methodLabel = $methodLabels[$receipt['payment_method'] ?? ''] ?? ucfirst((string)($receipt['payment_method'] ?? '—'));
                header('Content-Type: application/json');
                echo json_encode([
                    'success'      => true,
                    'message'      => $result['message'],
                    'receipt'      => $receipt,
                    'method_label' => $methodLabel,
                    'site_name'    => $siteName,
                ]);
                exit;
            }
            header('Location: payment-receipt.php?id=' . $id);
            exit;
        }

        $error = $result['message'];
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $error]);
            exit;
        }
    }
}

/* ---------------- Full-page fallback (direct visits) ---------------- */
$pageTitle = 'Record Payment';
include $basePath . 'includes/header.php';
include $basePath . 'includes/sidebar.php';
?>

<div class="flex-1 p-6 lg:p-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight mb-6">
            <i class="fa-solid fa-money-bill-wave text-brand-medium mr-3"></i>Record Payment
        </h1>

        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 rounded-lg text-sm font-bold"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm mb-6">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">Reservation Summary</h2>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-y-2 text-sm">
                <dt class="text-slate-500 dark:text-slate-400">Reservation #</dt>
                <dd class="font-mono text-slate-800 dark:text-white"><?= (int)$reservation['id'] ?></dd>

                <dt class="text-slate-500 dark:text-slate-400">Facility</dt>
                <dd class="text-slate-800 dark:text-white"><?= htmlspecialchars($reservation['facility_name']) ?></dd>

                <dt class="text-slate-500 dark:text-slate-400">Reserved To</dt>
                <dd class="text-slate-800 dark:text-white"><?= htmlspecialchars((string)$reservation['user_name']) ?></dd>

                <dt class="text-slate-500 dark:text-slate-400">Event</dt>
                <dd class="text-slate-800 dark:text-white"><?= htmlspecialchars($reservation['event_name']) ?></dd>

                <dt class="text-slate-500 dark:text-slate-400">Schedule</dt>
                <dd class="text-slate-800 dark:text-white">
                    <?= date('M d, Y H:i', strtotime($reservation['start_datetime'])) ?>
                    &rarr;
                    <?= date('M d, Y H:i', strtotime($reservation['end_datetime'])) ?>
                </dd>

                <dt class="text-slate-500 dark:text-slate-400">Payment Status</dt>
                <dd>
                    <?php $paid = ($reservation['payment_status'] ?? 'unpaid') === 'paid'; ?>
                    <span class="inline-block px-2 py-0.5 text-xs font-bold rounded-full <?= $paid ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?>">
                        <?= ucfirst($reservation['payment_status'] ?? 'unpaid') ?>
                    </span>
                </dd>
            </dl>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm">
            <form method="post" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="id" value="<?= (int)$reservation['id'] ?>">

                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Amount Received (₱)</label>
                    <input type="number" name="amount" step="0.01" min="0.01" required
                        class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none"
                        placeholder="0.00">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Payment Method</label>
                    <select name="method" required
                            class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                        <option value="">Select a method</option>
                        <option value="cash">Cash</option>
                        <option value="gcash">GCash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="check">Check</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Reference / OR No. (optional)</label>
                    <input type="text" name="reference" maxlength="100"
                        class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none"
                        placeholder="e.g., GCash ref #, check #, OR #">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Notes (optional)</label>
                    <textarea name="notes" rows="2"
                            class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none"
                            placeholder="Any additional remarks"></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                            class="px-6 py-2 bg-brand-dark hover:bg-[#0e4f62] text-white font-bold text-sm rounded-lg transition shadow-sm">
                        <i class="fa-solid fa-receipt mr-1"></i> Record Payment &amp; Generate Receipt
                    </button>
                    <a href="reservation.php"
                        class="px-6 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-sm rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include $basePath . 'includes/footer.php'; ?>
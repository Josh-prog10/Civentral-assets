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

$id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$receipt = $id > 0 ? $parkService->getReceipt($id) : null;

$methodLabels = [
    'cash'          => 'Cash',
    'gcash'         => 'GCash',
    'bank_transfer' => 'Bank Transfer',
    'check'         => 'Check',
    'other'         => 'Other',
];
$methodLabel = $methodLabels[$receipt['payment_method'] ?? ''] ?? ucfirst((string)($receipt['payment_method'] ?? '—'));

$siteName = defined('SITE_NAME') ? SITE_NAME : 'Parks & Recreation Reservation System';

// JSON mode for the in-page modal
if (isset($_GET['format']) && $_GET['format'] === 'json') {
    header('Content-Type: application/json');
    if (!$receipt) {
        echo json_encode(['success' => false, 'message' => 'Receipt not available.']);
        exit;
    }
    echo json_encode([
        'success'      => true,
        'receipt'      => $receipt,
        'method_label' => $methodLabel,
        'site_name'    => $siteName,
    ]);
    exit;
}

if (!$receipt) {
    http_response_code(404);
    $pageTitle = 'Receipt Not Found';
    include $basePath . 'includes/header.php';
    include $basePath . 'includes/sidebar.php';
    ?>
    <div class="flex-1 p-6 lg:p-8">
        <div class="max-w-xl mx-auto bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-8 text-center">
            <i class="fa-solid fa-circle-exclamation text-3xl text-rose-500 mb-3 block"></i>
            <h1 class="text-xl font-black text-slate-800 dark:text-white mb-2">Receipt not available</h1>
            <p class="text-slate-500 dark:text-slate-400 mb-4">No payment has been recorded for this reservation yet.</p>
            <a href="reservation.php" class="inline-block px-4 py-2 bg-brand-dark text-white text-sm font-bold rounded-lg">Back to Reservations</a>
        </div>
    </div>
    <?php
    include $basePath . 'includes/footer.php';
    exit;
}
?>
<!DOCTYPE html>
<!-- ... the existing <html>, <head>, CSS, and printable receipt markup below stay identical ... -->
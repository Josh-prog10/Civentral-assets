<?php
declare(strict_types=1);

$basePath = '../../';
require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/LguService.php';

use App\Service\LguService;

$lguService = new LguService($pdo);

if (!$isSuperAdmin && !$hasResourceAccess(['lgu', 'reservation'])) {
    header('Location: ' . $basePath . 'pages/dashboard.php?error=access_denied');
    exit;
}

$message = $error = '';
if (empty($_SESSION['lgu_csrf_token'])) {
    $_SESSION['lgu_csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['lgu_csrf_token'];

$filters = [];
if (isset($_GET['facility_id']) && is_numeric($_GET['facility_id'])) {
    $filters['facility_id'] = (int)$_GET['facility_id'];
}
if (isset($_GET['status']) && in_array($_GET['status'], ['pending','approved','rejected','cancelled','completed'], true)) {
    $filters['status'] = $_GET['status'];
}
if (isset($_GET['payment_status']) && in_array($_GET['payment_status'], ['unpaid','paid','refunded'], true)) {
    $filters['payment_status'] = $_GET['payment_status'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($csrfToken, $_POST['csrf_token'])) {
        $error = 'Invalid request.';
    } else {
        if (isset($_POST['update_status'])) {
            $id = (int)($_POST['id'] ?? 0);
            $status = $_POST['status'] ?? '';
            if ($id < 1 || !in_array($status, ['pending','approved','rejected','cancelled','completed'], true)) {
                $error = 'Invalid data.';
            } else {
                try {
                    if ($lguService->updateReservationStatus($id, $status)) {
                        $message = 'Status updated.';
                    } else {
                        if ($status === 'approved') {
                            $error = 'Cannot approve: reservation is unpaid, expired, or invalid. Record payment first.';
                        } else {
                            $error = 'Update failed. The reservation may already be expired or invalid.';
                        }
                    }
                } catch (Exception $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        } elseif (isset($_POST['delete'])) {
            $id = (int)($_POST['id'] ?? 0);
            if ($id < 1) {
                $error = 'Invalid reservation ID.';
            } else {
                try {
                    if ($lguService->deleteReservation($id)) {
                        $message = 'Reservation deleted.';
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

$reservations = $lguService->getReservations($filters);
$facilities = $lguService->getFacilities(true);
$pageTitle = 'LGU Reservations';
include $basePath . 'includes/header.php';
include $basePath . 'includes/sidebar.php';
?>

<style>
    /* Hover-to-confirm delete button */
    .delete-btn {
        transition: background-color .15s ease, transform .15s ease, box-shadow .15s ease;
    }
    .delete-btn.armed {
        background-color: #be123c;
        transform: scale(1.05);
        box-shadow: 0 0 0 2px rgba(190, 18, 60, .25);
    }

    @media print {
        body * { visibility: hidden !important; }
        #receiptModal, #receiptModal * { visibility: visible !important; }
        #receiptModal {
            position: fixed !important;
            inset: 0 !important;
            background: #fff !important;
            display: block !important;
            overflow: visible !important;
        }
        #receiptModal .receipt-backdrop,
        #receiptModal .receipt-toolbar { display: none !important; }
        #receiptModal .receipt-scroll {
            max-height: none !important;
            overflow: visible !important;
            box-shadow: none !important;
            border: none !important;
            border-radius: 0 !important;
        }
        #receiptBody { padding: 0 !important; }
        @page { margin: 12mm; }
    }
</style>

<div class="flex-1 p-6 lg:p-8">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">
                <i class="fa-solid fa-calendar-check text-brand-medium mr-3"></i>Reservations
            </h1>
            <div class="flex gap-3">
                <a href="<?= $basePath ?>pages/lgu-facilities/reserve.php" class="text-sm font-bold bg-brand-dark hover:bg-[#0e4f62] text-white px-4 py-2 rounded-lg transition shadow-sm">
                    <i class="fa-solid fa-plus mr-1"></i> New Reservation
                </a>
                <a href="<?= $basePath ?>pages/lgu-facilities/ldb.php" class="text-sm font-bold text-brand-medium hover:text-brand-dark dark:hover:text-brand-light transition">Back</a>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="mb-4 p-3 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 rounded-lg text-sm font-bold"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 rounded-lg text-sm font-bold"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm mb-6 flex flex-wrap gap-3">
            <form method="get" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Facility</label>
                    <select name="facility_id" class="px-3 py-1.5 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                        <option value="">All</option>
                        <?php foreach ($facilities as $f): ?>
                            <option value="<?= $f['id'] ?>" <?= (isset($_GET['facility_id']) && $_GET['facility_id']==$f['id']) ? 'selected' : '' ?>><?= htmlspecialchars($f['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Status</label>
                    <select name="status" class="px-3 py-1.5 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                        <option value="">All</option>
                        <option value="pending"   <?= (isset($_GET['status']) && $_GET['status']=='pending')?'selected':'' ?>>Pending</option>
                        <option value="approved"  <?= (isset($_GET['status']) && $_GET['status']=='approved')?'selected':'' ?>>Approved</option>
                        <option value="rejected"  <?= (isset($_GET['status']) && $_GET['status']=='rejected')?'selected':'' ?>>Rejected</option>
                        <option value="cancelled" <?= (isset($_GET['status']) && $_GET['status']=='cancelled')?'selected':'' ?>>Cancelled</option>
                        <option value="completed" <?= (isset($_GET['status']) && $_GET['status']=='completed')?'selected':'' ?>>Completed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Payment</label>
                    <select name="payment_status" class="px-3 py-1.5 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                        <option value="">All</option>
                        <option value="unpaid" <?= (isset($_GET['payment_status']) && $_GET['payment_status']=='unpaid')?'selected':'' ?>>Unpaid</option>
                        <option value="paid" <?= (isset($_GET['payment_status']) && $_GET['payment_status']=='paid')?'selected':'' ?>>Paid</option>
                        <option value="refunded" <?= (isset($_GET['payment_status']) && $_GET['payment_status']=='refunded')?'selected':'' ?>>Refunded</option>
                    </select>
                </div>
                <button type="submit" class="px-4 py-1.5 bg-brand-medium text-white text-sm font-bold rounded-lg hover:bg-brand-dark transition">Filter</button>
                <a href="<?= $basePath ?>pages/lgu-facilities/reservations.php" class="px-4 py-1.5 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-bold rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition">Clear</a>
            </form>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3 text-left">#</th>
                            <th class="px-6 py-3 text-left">Purpose</th>
                            <th class="px-6 py-3 text-left">Facility</th>
                            <th class="px-6 py-3 text-left">Reserve To</th>
                            <th class="px-6 py-3 text-left">Start</th>
                            <th class="px-6 py-3 text-left">End</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-left">Payment</th>
                            <th class="px-6 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php foreach ($reservations as $r): ?>
                            <?php $isTerminal = in_array($r['status'], ['cancelled', 'rejected', 'completed'], true); ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                <td class="px-6 py-3 font-mono text-slate-600 dark:text-slate-400"><?= $r['id'] ?></td>
                                <td class="px-6 py-3 font-medium text-slate-700 dark:text-slate-300"><?= htmlspecialchars($r['purpose']) ?></td>
                                <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= htmlspecialchars($r['facility_name']) ?></td>
                                <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= htmlspecialchars((string)$r['user_name']) ?></td>
                                <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= date('M d, Y H:i', strtotime($r['start_datetime'])) ?></td>
                                <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?= date('M d, Y H:i', strtotime($r['end_datetime'])) ?></td>
                                <td class="px-6 py-3">
                                    <?php
                                    $isExpired  = ($r['status'] === 'cancelled' && ($r['cancelled_reason'] ?? '') === 'expired');
                                    $statusText = $isExpired ? 'Auto-cancelled' : ucfirst($r['status']);
                                    $statusClass = match ($r['status']) {
                                        'pending'   => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                                        'approved'  => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                                        'rejected'  => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300',
                                        'cancelled' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400',
                                        'completed' => 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300',
                                        default     => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
                                    };
                                    ?>
                                    <span class="inline-block px-2 py-0.5 text-xs font-bold rounded-full <?= $statusClass ?>">
                                        <?= $statusText ?>
                                    </span>
                                </td>
                                <td class="px-6 py-3">
                                    <?php
                                    $pStatus = $r['payment_status'] ?? 'unpaid';
                                    $pClass  = match ($pStatus) {
                                        'paid'     => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                                        'refunded' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400',
                                        default    => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                                    };
                                    ?>
                                    <span class="inline-block px-2 py-0.5 text-xs font-bold rounded-full <?= $pClass ?>">
                                        <?= ucfirst($pStatus) ?>
                                    </span>
                                    <?php if (!empty($r['payment_amount']) && (float)$r['payment_amount'] > 0): ?>
                                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">₱<?= number_format((float)$r['payment_amount'], 2) ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($r['receipt_number'])): ?>
                                        <div class="text-[10px] font-mono text-slate-400 dark:text-slate-500 mt-0.5"><?= htmlspecialchars($r['receipt_number']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        <?php if ($isTerminal): ?>
                                            <span class="text-xs text-slate-400 dark:text-slate-500">Read-only</span>
                                        <?php else: ?>
                                            <form method="post" class="inline-flex items-center gap-1">
                                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                                <select name="status" class="px-1 py-0.5 text-xs border border-slate-300 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
                                                    <option value="pending"   <?= $r['status']=='pending'?'selected':'' ?>>Pending</option>
                                                    <option value="approved"  <?= $r['status']=='approved'?'selected':'' ?>>Approved</option>
                                                    <option value="rejected"  <?= $r['status']=='rejected'?'selected':'' ?>>Rejected</option>
                                                    <option value="cancelled" <?= $r['status']=='cancelled'?'selected':'' ?>>Cancelled</option>
                                                    <option value="completed" <?= $r['status']=='completed'?'selected':'' ?>>Completed</option>
                                                </select>
                                                <button type="submit" name="update_status" class="px-2 py-0.5 bg-brand-medium text-white text-xs rounded hover:bg-brand-dark transition">Update</button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if (($r['payment_status'] ?? 'unpaid') === 'paid'): ?>
                                            <button type="button"
                                                class="receipt-btn px-2 py-0.5 bg-emerald-500 text-white text-xs rounded hover:bg-emerald-600 transition"
                                                data-id="<?= (int)$r['id'] ?>">
                                                <i class="fa-solid fa-receipt mr-0.5"></i>Receipt
                                            </button>
                                        <?php elseif (!$isTerminal): ?>
                                            <button type="button"
                                                class="pay-btn px-2 py-0.5 bg-brand-dark text-white text-xs rounded hover:bg-[#0e4f62] transition"
                                                data-id="<?= (int)$r['id'] ?>"
                                                data-facility="<?= htmlspecialchars($r['facility_name']) ?>"
                                                data-user="<?= htmlspecialchars((string)$r['user_name']) ?>"
                                                data-purpose="<?= htmlspecialchars($r['purpose']) ?>"
                                                data-schedule="<?= date('M d, Y H:i', strtotime($r['start_datetime'])) ?> → <?= date('M d, Y H:i', strtotime($r['end_datetime'])) ?>"
                                                data-payment-status="<?= htmlspecialchars($r['payment_status'] ?? 'unpaid') ?>">
                                                <i class="fa-solid fa-money-bill-wave mr-0.5"></i>Pay
                                            </button>
                                        <?php endif; ?>

                                        <form method="post" class="inline-flex delete-form">
                                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                            <button type="submit" name="delete"
                                                class="delete-btn px-2 py-0.5 bg-rose-500 text-white text-xs rounded whitespace-nowrap"
                                                data-armed="0">
                                                <i class="fa-solid fa-trash mr-0.5"></i><span class="delete-label">Delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($reservations)): ?>
                            <tr><td colspan="9" class="px-6 py-8 text-center text-slate-400 dark:text-slate-500">No reservations found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ============ PAYMENT MODAL ============ -->
<div id="paymentModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" data-close-payment></div>
    <div class="relative h-full w-full flex items-center justify-center p-4 pointer-events-none">
        <div class="pointer-events-auto w-full max-w-md bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-2xl max-h-[85vh] flex flex-col">
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-800 dark:text-white">
                    <i class="fa-solid fa-money-bill-wave text-brand-medium mr-1.5"></i>Record Payment
                </h2>
                <button type="button" data-close-payment class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="paymentForm" class="p-4 space-y-3 overflow-y-auto text-xs">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="id" id="pay-id" value="">

                <div class="bg-slate-50 dark:bg-slate-800/50 rounded-lg p-3 text-xs">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-y-1.5 gap-x-3">
                        <dt class="text-slate-500 dark:text-slate-400">Reservation #</dt>
                        <dd class="font-mono text-slate-800 dark:text-white" id="pay-res-id">—</dd>
                        <dt class="text-slate-500 dark:text-slate-400">Facility</dt>
                        <dd class="text-slate-800 dark:text-white" id="pay-facility">—</dd>
                        <dt class="text-slate-500 dark:text-slate-400">Reserved To</dt>
                        <dd class="text-slate-800 dark:text-white" id="pay-user">—</dd>
                        <dt class="text-slate-500 dark:text-slate-400">Purpose</dt>
                        <dd class="text-slate-800 dark:text-white" id="pay-purpose">—</dd>
                        <dt class="text-slate-500 dark:text-slate-400">Schedule</dt>
                        <dd class="text-slate-800 dark:text-white" id="pay-schedule">—</dd>
                    </dl>
                </div>

                <div id="pay-error" class="hidden p-2.5 bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 rounded-lg text-xs font-bold"></div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Amount Received (₱)</label>
                    <input type="number" name="amount" step="0.01" min="0.01" required
                        class="w-full px-3 py-1.5 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-xs focus:ring-2 focus:ring-brand-medium focus:outline-none"
                        placeholder="0.00">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Payment Method</label>
                    <select name="method" required
                            class="w-full px-3 py-1.5 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-xs focus:ring-2 focus:ring-brand-medium focus:outline-none">
                        <option value="">Select a method</option>
                        <option value="cash">Cash</option>
                        <option value="gcash">GCash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="check">Check</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Reference / OR No. (optional)</label>
                    <input type="text" name="reference" maxlength="100"
                        class="w-full px-3 py-1.5 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-xs focus:ring-2 focus:ring-brand-medium focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Notes (optional)</label>
                    <textarea name="notes" rows="2"
                            class="w-full px-3 py-1.5 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-xs focus:ring-2 focus:ring-brand-medium focus:outline-none"></textarea>
                </div>

                <div class="flex gap-2 pt-1">
                    <button type="submit" id="pay-submit"
                            class="px-4 py-1.5 bg-brand-dark hover:bg-[#0e4f62] text-white font-bold text-xs rounded-lg transition shadow-sm">
                        <i class="fa-solid fa-receipt mr-1"></i> Record &amp; Generate Receipt
                    </button>
                    <button type="button" data-close-payment
                            class="px-4 py-1.5 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============ RECEIPT MODAL ============ -->
<div id="receiptModal" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm receipt-backdrop" data-close-receipt></div>
    <div class="relative h-full w-full flex items-center justify-center p-4 pointer-events-none">
        <div class="pointer-events-auto w-full max-w-xl bg-white dark:bg-slate-900 rounded-xl shadow-2xl max-h-[85vh] flex flex-col receipt-scroll">
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between receipt-toolbar">
                <h2 class="text-sm font-bold text-slate-800 dark:text-white">
                    <i class="fa-solid fa-receipt text-emerald-500 mr-1.5"></i>Official Receipt
                </h2>
                <div class="flex items-center gap-2">
                    <button type="button" id="receiptPrintBtn"
                            class="px-2.5 py-1 bg-brand-dark hover:bg-[#0e4f62] text-white text-[11px] font-bold rounded-lg transition">
                        <i class="fa-solid fa-print mr-1"></i> Print
                    </button>
                    <button type="button" data-close-receipt
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>
            <div id="receiptBody" class="p-4 overflow-y-auto text-xs"></div>
        </div>
    </div>
</div>

<script>
(function () {
    const paymentModal = document.getElementById('paymentModal');
    const receiptModal = document.getElementById('receiptModal');
    const paymentForm  = document.getElementById('paymentForm');
    const payError     = document.getElementById('pay-error');
    const paySubmit    = document.getElementById('pay-submit');
    const receiptBody  = document.getElementById('receiptBody');

    let needsReload = false;

    function esc(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => (
            { '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c]
        ));
    }
    function fmtDate(s) {
        if (!s) return '—';
        const d = new Date(String(s).replace(' ', 'T'));
        if (isNaN(d.getTime())) return s;
        return d.toLocaleString('en-US', {
            month: 'short', day: '2-digit', year: 'numeric',
            hour: '2-digit', minute: '2-digit', hour12: false
        });
    }
    function money(n) {
        return '₱' + Number(n || 0).toLocaleString('en-US', {
            minimumFractionDigits: 2, maximumFractionDigits: 2
        });
    }
    function lockScroll(on) {
        document.body.style.overflow = on ? 'hidden' : '';
    }

    function openPaymentModal(btn) {
        paymentForm.reset();
        document.getElementById('pay-id').value             = btn.dataset.id;
        document.getElementById('pay-res-id').textContent   = '#' + btn.dataset.id;
        document.getElementById('pay-facility').textContent = btn.dataset.facility || '—';
        document.getElementById('pay-user').textContent     = btn.dataset.user     || '—';
        document.getElementById('pay-purpose').textContent  = btn.dataset.purpose  || '—';
        document.getElementById('pay-schedule').textContent = btn.dataset.schedule || '—';

        payError.classList.add('hidden');
        payError.textContent = '';

        paymentModal.classList.remove('hidden');
        lockScroll(true);
    }
    function closePaymentModal() {
        paymentModal.classList.add('hidden');
        if (receiptModal.classList.contains('hidden')) lockScroll(false);
    }

    function openReceiptModal() {
        receiptModal.classList.remove('hidden');
        lockScroll(true);
    }
    function closeReceiptModal() {
        receiptModal.classList.add('hidden');
        lockScroll(false);
        if (needsReload) {
            needsReload = false;
            window.location.reload();
        }
    }

    document.querySelectorAll('.pay-btn').forEach(btn => {
        btn.addEventListener('click', () => openPaymentModal(btn));
    });
    document.querySelectorAll('.receipt-btn').forEach(btn => {
        btn.addEventListener('click', () => loadReceipt(btn.dataset.id));
    });
    document.querySelectorAll('[data-close-payment]').forEach(el => {
        el.addEventListener('click', closePaymentModal);
    });
    document.querySelectorAll('[data-close-receipt]').forEach(el => {
        el.addEventListener('click', closeReceiptModal);
    });
    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        if (!receiptModal.classList.contains('hidden'))      closeReceiptModal();
        else if (!paymentModal.classList.contains('hidden')) closePaymentModal();
    });

    /* ---- Hover-to-confirm delete ---- */
    document.querySelectorAll('.delete-form').forEach(form => {
        const btn = form.querySelector('.delete-btn');
        if (!btn) return;
        const label = btn.querySelector('.delete-label');

        const arm = () => {
            btn.dataset.armed = '1';
            btn.classList.add('armed');
            if (label) label.textContent = 'Confirm?';
        };
        const disarm = () => {
            btn.dataset.armed = '0';
            btn.classList.remove('armed');
            if (label) label.textContent = 'Delete';
        };

        btn.addEventListener('mouseenter', arm);
        btn.addEventListener('mouseleave', disarm);
        btn.addEventListener('blur', disarm);

        btn.addEventListener('click', (e) => {
            if (btn.dataset.armed !== '1') {
                e.preventDefault();
                arm();
            }
        });
    });

    paymentForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        payError.classList.add('hidden');

        const original = paySubmit.innerHTML;
        paySubmit.disabled = true;
        paySubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Processing...';

        try {
            const res = await fetch('lgu-payment.php', {
                method: 'POST',
                body: new FormData(paymentForm),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });
            const data = await res.json();

            if (!data.success) {
                payError.textContent = data.message || 'Payment failed.';
                payError.classList.remove('hidden');
                return;
            }

            paymentModal.classList.add('hidden');
            needsReload = true;
            renderReceipt(data.receipt, data.method_label, data.site_name);
            openReceiptModal();
        } catch (err) {
            payError.textContent = 'Network error: ' + err.message;
            payError.classList.remove('hidden');
        } finally {
            paySubmit.disabled = false;
            paySubmit.innerHTML = original;
        }
    });

    async function loadReceipt(id) {
        try {
            const res = await fetch('lgu-receipt.php?id=' + encodeURIComponent(id) + '&format=json', {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            const data = await res.json();
            if (!data.success) { alert(data.message || 'Receipt not found.'); return; }
            renderReceipt(data.receipt, data.method_label, data.site_name);
            openReceiptModal();
        } catch (err) {
            alert('Network error: ' + err.message);
        }
    }

    document.getElementById('receiptPrintBtn').addEventListener('click', () => window.print());

    function renderReceipt(r, methodLabel, siteName) {
        if (!r) {
            receiptBody.innerHTML = '<p class="text-slate-500">No receipt data.</p>';
            return;
        }
        const H   = 'font-size:9px;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:1px;';
        const V   = 'font-size:11.5px;color:#1e293b;font-weight:600;';
        const SEC = 'font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:#94a3b8;margin:12px 0 6px;';

        receiptBody.innerHTML = `
            <div style="font-family:'Segoe UI',Tahoma,Arial,sans-serif;color:#1e293b;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;border-bottom:2px solid #0f5c73;padding-bottom:12px;margin-bottom:14px;">
                    <div style="display:flex;gap:10px;align-items:center;">
                        <div style="width:36px;height:36px;border-radius:8px;background:#0f5c73;color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:900;">LGU</div>
                        <div>
                            <div style="font-size:14px;font-weight:800;color:#0f5c73;margin:0;">${esc(siteName || 'LGU')}</div>
                            <div style="font-size:10.5px;color:#64748b;">Official Payment Receipt</div>
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:9px;letter-spacing:.08em;text-transform:uppercase;color:#64748b;">Receipt No.</div>
                        <div style="font-family:monospace;font-size:11.5px;font-weight:700;">${esc(r.receipt_number)}</div>
                        <div style="display:inline-block;background:#dcfce7;color:#166534;padding:2px 8px;border-radius:999px;font-size:9px;font-weight:800;text-transform:uppercase;margin-top:4px;">Paid</div>
                    </div>
                </div>

                <h3 style="${SEC}">Payer Information</h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px 18px;">
                    <div><div style="${H}">Reserved By</div><div style="${V}">${esc(r.user_name || ('User #' + r.user_id))}</div></div>
                    <div><div style="${H}">Reservation No.</div><div style="${V}">#${esc(r.id)}</div></div>
                </div>

                <h3 style="${SEC}">Facility &amp; Schedule</h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px 18px;">
                    <div><div style="${H}">Facility</div><div style="${V}">${esc(r.facility_name || '—')}</div></div>
                    <div><div style="${H}">Location</div><div style="${V}">${esc(r.facility_location || '—')}</div></div>
                    <div><div style="${H}">Start</div><div style="${V}">${esc(fmtDate(r.start_datetime))}</div></div>
                    <div><div style="${H}">End</div><div style="${V}">${esc(fmtDate(r.end_datetime))}</div></div>
                    <div style="grid-column:1/-1;"><div style="${H}">Purpose</div><div style="${V}">${esc(r.purpose || '—')}</div></div>
                </div>

                <h3 style="${SEC}">Payment Details</h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px 18px;">
                    <div><div style="${H}">Date Paid</div><div style="${V}">${esc(fmtDate(r.paid_at))}</div></div>
                    <div><div style="${H}">Payment Method</div><div style="${V}">${esc(methodLabel || '—')}</div></div>
                    <div><div style="${H}">Reference / OR No.</div><div style="${V}">${esc(r.payment_reference || '—')}</div></div>
                    <div><div style="${H}">Remarks</div><div style="${V}">${esc(r.payment_notes || '—')}</div></div>
                </div>

                <div style="background:#f8fafc;border:1px dashed #cbd5e1;border-radius:8px;padding:12px 16px;margin-top:16px;display:flex;justify-content:space-between;align-items:center;">
                    <div style="font-size:10.5px;color:#64748b;text-transform:uppercase;letter-spacing:.08em;">Total Amount Paid</div>
                    <div style="font-size:20px;font-weight:900;color:#0f5c73;">${money(r.payment_amount)}</div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:30px;margin-top:34px;">
                    <div style="text-align:center;font-size:10.5px;color:#475569;">
                        <div style="border-top:1px solid #94a3b8;padding-top:4px;margin-top:26px;">Received By (Cashier / Treasurer)</div>
                    </div>
                    <div style="text-align:center;font-size:10.5px;color:#475569;">
                        <div style="border-top:1px solid #94a3b8;padding-top:4px;margin-top:26px;">Approved By (LGU Officer)</div>
                    </div>
                </div>

                <div style="margin-top:16px;font-size:10px;color:#94a3b8;text-align:center;line-height:1.5;">
                    This receipt is system-generated and valid without a signature.<br>
                    Generated on ${esc(new Date().toLocaleString('en-US', { dateStyle: 'long', timeStyle: 'short' }))}.
                </div>
            </div>
        `;
    }
})();
</script>

<?php include $basePath . 'includes/footer.php'; ?>
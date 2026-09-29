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

$id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$receipt = $id > 0 ? $lguService->getReceipt($id) : null;

$methodLabels = [
    'cash'          => 'Cash',
    'gcash'         => 'GCash',
    'bank_transfer' => 'Bank Transfer',
    'check'         => 'Check',
    'other'         => 'Other',
];
$methodLabel = $methodLabels[$receipt['payment_method'] ?? ''] ?? ucfirst((string)($receipt['payment_method'] ?? '—'));

// JSON mode for the in-page modal
if (isset($_GET['format']) && $_GET['format'] === 'json') {
    header('Content-Type: application/json');
    if (!$receipt) {
        echo json_encode(['success' => false, 'message' => 'Receipt not found.']);
        exit;
    }
    echo json_encode([
        'success'      => true,
        'receipt'      => $receipt,
        'method_label' => $methodLabel,
        'site_name'    => $siteName ?? 'LGU',
    ]);
    exit;
}

if (!$receipt) {
    header('Location: ' . $basePath . 'pages/lgu-facilities/reservations.php?error=not_found');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Receipt <?= htmlspecialchars($receipt['receipt_number']) ?></title>
<style>
    * { box-sizing: border-box; }
    body {
        font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
        background: #eef2f6;
        color: #1e293b;
        margin: 0;
        padding: 32px 16px;
    }
    .flash {
        max-width: 720px;
        margin: 0 auto 16px;
        padding: 10px 14px;
        background: #dcfce7;
        color: #166534;
        border: 1px solid #86efac;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
    }
    .toolbar {
        max-width: 720px;
        margin: 0 auto 16px;
        display: flex;
        justify-content: space-between;
        gap: 12px;
    }
    .toolbar a, .toolbar button {
        font: inherit;
        font-weight: 700;
        font-size: 14px;
        border-radius: 8px;
        padding: 10px 18px;
        cursor: pointer;
        text-decoration: none;
        border: none;
    }
    .btn-back { background: #e2e8f0; color: #334155; }
    .btn-print { background: #0f5c73; color: #fff; }
    .btn-print:hover { background: #0e4f62; }

    .receipt {
        max-width: 720px;
        margin: 0 auto;
        background: #fff;
        border-radius: 12px;
        padding: 40px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .08);
    }
    .head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        border-bottom: 2px solid #0f5c73;
        padding-bottom: 20px;
        margin-bottom: 24px;
    }
    .brand { display: flex; align-items: center; gap: 12px; }
    .brand-mark {
        width: 48px; height: 48px;
        border-radius: 10px;
        background: #0f5c73;
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px; font-weight: 900;
    }
    .brand h1 { font-size: 18px; margin: 0 0 4px; color: #0f5c73; }
    .brand p  { margin: 0; font-size: 12px; color: #64748b; }

    .receipt-meta { text-align: right; }
    .receipt-meta .label { font-size: 11px; letter-spacing: .08em; text-transform: uppercase; color: #64748b; }
    .receipt-meta .value { font-family: 'Courier New', monospace; font-size: 15px; font-weight: 700; color: #0f172a; }
    .receipt-meta .status {
        margin-top: 8px; display: inline-block;
        background: #dcfce7; color: #166534;
        padding: 4px 10px; border-radius: 999px;
        font-size: 11px; font-weight: 800; letter-spacing: .05em;
        text-transform: uppercase;
    }

    h2.section {
        font-size: 11px; letter-spacing: .1em; text-transform: uppercase;
        color: #94a3b8; margin: 24px 0 10px;
    }
    .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px 24px; }
    .field .label { font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 2px; }
    .field .value { font-size: 14px; color: #1e293b; font-weight: 600; }

    .amount-box {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 10px;
        padding: 18px 22px;
        margin-top: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .amount-box .label { font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: .08em; }
    .amount-box .value { font-size: 26px; font-weight: 900; color: #0f5c73; }

    .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 60px; }
    .sig { text-align: center; font-size: 12px; color: #475569; }
    .sig .line { border-top: 1px solid #94a3b8; padding-top: 6px; margin-top: 40px; }

    .footer-note {
        margin-top: 32px; font-size: 11px; color: #94a3b8;
        text-align: center; line-height: 1.6;
    }

    @media print {
        body { background: #fff; padding: 0; }
        .toolbar, .flash { display: none !important; }
        .receipt { box-shadow: none; border-radius: 0; padding: 24px; max-width: 100%; }
        @page { margin: 12mm; }
    }
</style>
</head>
<body>

<?php if (!empty($_SESSION['lgu_flash'])): ?>
    <div class="flash"><?= htmlspecialchars($_SESSION['lgu_flash']) ?></div>
    <?php unset($_SESSION['lgu_flash']); ?>
<?php endif; ?>

<div class="toolbar">
    <a class="btn-back" href="reservations.php">&larr; Back to Reservations</a>
    <button class="btn-print" onclick="window.print()">🖨️ Print Receipt</button>
</div>

<div class="receipt">
    <div class="head">
        <div class="brand">
            <div class="brand-mark">LGU</div>
            <div>
                <h1><?= htmlspecialchars($siteName ?? 'LGU') ?></h1>
                <p>Official Payment Receipt</p>
            </div>
        </div>
        <div class="receipt-meta">
            <div class="label">Receipt No.</div>
            <div class="value"><?= htmlspecialchars($receipt['receipt_number']) ?></div>
            <div class="status">Paid</div>
        </div>
    </div>

    <h2 class="section">Payer Information</h2>
    <div class="grid">
        <div class="field">
            <div class="label">Reserved By</div>
            <div class="value"><?= htmlspecialchars((string)($receipt['user_name'] ?? ('User #' . $receipt['user_id']))) ?></div>
        </div>
        <div class="field">
            <div class="label">Reservation No.</div>
            <div class="value">#<?= (int)$receipt['id'] ?></div>
        </div>
    </div>

    <h2 class="section">Facility &amp; Schedule</h2>
    <div class="grid">
        <div class="field">
            <div class="label">Facility</div>
            <div class="value"><?= htmlspecialchars($receipt['facility_name']) ?></div>
        </div>
        <div class="field">
            <div class="label">Location</div>
            <div class="value"><?= htmlspecialchars((string)$receipt['facility_location']) ?></div>
        </div>
        <div class="field">
            <div class="label">Start</div>
            <div class="value"><?= date('M d, Y H:i', strtotime($receipt['start_datetime'])) ?></div>
        </div>
        <div class="field">
            <div class="label">End</div>
            <div class="value"><?= date('M d, Y H:i', strtotime($receipt['end_datetime'])) ?></div>
        </div>
        <div class="field" style="grid-column: 1 / -1;">
            <div class="label">Purpose</div>
            <div class="value"><?= htmlspecialchars($receipt['purpose']) ?></div>
        </div>
    </div>

    <h2 class="section">Payment Details</h2>
    <div class="grid">
        <div class="field">
            <div class="label">Date Paid</div>
            <div class="value"><?= $receipt['paid_at'] ? date('M d, Y H:i', strtotime($receipt['paid_at'])) : '—' ?></div>
        </div>
        <div class="field">
            <div class="label">Payment Method</div>
            <div class="value"><?= htmlspecialchars($methodLabel) ?></div>
        </div>
        <div class="field">
            <div class="label">Reference / OR No.</div>
            <div class="value"><?= htmlspecialchars((string)($receipt['payment_reference'] ?: '—')) ?></div>
        </div>
        <div class="field">
            <div class="label">Remarks</div>
            <div class="value"><?= htmlspecialchars((string)($receipt['payment_notes'] ?: '—')) ?></div>
        </div>
    </div>

    <div class="amount-box">
        <div class="label">Total Amount Paid</div>
        <div class="value">₱<?= number_format((float)$receipt['payment_amount'], 2) ?></div>
    </div>

    <div class="signatures">
        <div class="sig">
            <div class="line">Received By (Cashier / Treasurer)</div>
        </div>
        <div class="sig">
            <div class="line">Approved By (LGU Officer)</div>
        </div>
    </div>

    <div class="footer-note">
        This receipt is system-generated and valid without a signature.<br>
        Generated on <?= date('F d, Y H:i') ?>.
    </div>
</div>

</body>
</html>
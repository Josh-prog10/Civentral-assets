<?php
// POST /api/employee/verify-reset-otp

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

// CORS
$allowedOrigins = [
    'http://localhost',
    'http://localhost:80',
    'http://localhost:3000',
    'http://127.0.0.1',
    'http://127.0.0.1:80'
];

if (isset($_SERVER['HTTP_ORIGIN'])) {
    $origin = $_SERVER['HTTP_ORIGIN'];
    if (in_array($origin, $allowedOrigins) || preg_match('/^http:\/\/(localhost|127\.0\.0\.1)(:\d+)?$/', $origin)) {
        header("Access-Control-Allow-Origin: {$origin}");
        header('Access-Control-Allow-Credentials: true');
    }
}

header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/Services/AuditLogger.php';

// Response helper
function respond(array $payload, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

// Method guard
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['status' => 'error', 'message' => 'Method Not Allowed.'], 405);
}

// Session check
if (empty($_SESSION['reset_otp_user_id'])) {
    respond(['status' => 'error', 'message' => 'Session expired. Please restart the password reset process.'], 401);
}

// Input
$input   = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$otpCode = trim($input['otp'] ?? $input['otp_code'] ?? '');

if (empty($otpCode) || strlen($otpCode) !== 6 || !ctype_digit($otpCode)) {
    respond(['status' => 'error', 'message' => 'Please enter a valid 6-digit verification code.'], 400);
}

$userId = intval($_SESSION['reset_otp_user_id']);

// Main
try {
    // Fetch OTP
    $otps = $db->query(
        "SELECT * FROM user_otps
          WHERE user_id = :user_id AND purpose = 'Password Reset' AND is_used = 0
          ORDER BY otp_id DESC LIMIT 1",
        ['user_id' => $userId]
    );

    if (empty($otps)) {
        respond(['status' => 'error', 'message' => 'No active password reset code found. Please request a new code.'], 400);
    }

    $otpRecord       = $otps[0];
    $otpId           = intval($otpRecord['otp_id']);
    $currentAttempts = intval($otpRecord['attempts'] ?? 0) + 1;

    // Expiry check
    if (strtotime($otpRecord['expires_at']) < time()) {
        try { $db->update('user_otps', ['is_used' => 1], ['otp_id' => $otpId]); } catch (Throwable $ex) {}
        respond(['status' => 'error', 'message' => 'Verification code has expired. Please request a new password reset code.'], 400);
    }

    // Attempts check
    if ($currentAttempts > 5) {
        try { $db->update('user_otps', ['is_used' => 1], ['otp_id' => $otpId]); } catch (Throwable $ex) {}
        unset($_SESSION['reset_otp_user_id']);
        respond(['status' => 'error', 'message' => 'Too many failed attempts. Verification code invalidated. Please request a new password reset code.'], 403);
    }

    // Increment attempts
    try { $db->update('user_otps', ['attempts' => $currentAttempts], ['otp_id' => $otpId]); } catch (Throwable $ex) {}

    // Code match
    if ($otpRecord['otp_code'] !== $otpCode) {
        $remaining   = 5 - $currentAttempts;
        $failMessage = ($remaining > 0)
            ? "Invalid verification code. You have {$remaining} attempt(s) remaining."
            : "Maximum verification attempts exceeded. Please request a new password reset code.";
        respond(['status' => 'error', 'message' => $failMessage], 400);
    }

    // Mark used
    try {
        $db->update('user_otps', ['is_used' => 1, 'verified_at' => date('Y-m-d H:i:s')], ['otp_id' => $otpId]);
    } catch (Throwable $ex) {}

    // Promote session
    unset($_SESSION['reset_otp_user_id']);
    $_SESSION['reset_otp_verified_user_id'] = $userId;

    // Audit
    \App\Services\AuditLogger::log([
        'action'        => 'Forgot Password OTP Verified',
        'target_table'  => 'users',
        'target_id'     => (string)$userId,
        'description'   => 'Employee successfully verified password reset OTP.',
        'actor_user_id' => $userId
    ]);

    respond(['status' => 'success', 'message' => 'Verification successful. You may now set a new password.']);

} catch (Throwable $e) {
    error_log("Verify Reset OTP API Error: " . $e->getMessage());
    respond(['status' => 'error', 'message' => 'Failed to verify code. Please try again later.'], 500);
}
?>
<?php
// POST /api/employee/reset-password

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
if (empty($_SESSION['reset_otp_verified_user_id'])) {
    respond(['status' => 'error', 'message' => 'Unauthorized. Please complete the password reset verification process first.'], 401);
}

// Input
$input   = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$newPass = trim($input['new_password'] ?? $input['password'] ?? '');

if (empty($newPass)) {
    respond(['status' => 'error', 'message' => 'New password is required.'], 422);
}

// Complexity check
if (
    strlen($newPass) < 8 ||
    !preg_match('/[A-Z]/', $newPass) ||
    !preg_match('/[a-z]/', $newPass) ||
    !preg_match('/[0-9]/', $newPass) ||
    !preg_match('/[!@#$&*^%\-_+=?<>]/', $newPass)
) {
    respond(['status' => 'error', 'message' => 'Password must be at least 8 characters long and contain uppercase, lowercase, numeric, and special characters.'], 422);
}

$userId = intval($_SESSION['reset_otp_verified_user_id']);

// Main
try {
    // Fetch user
    $users = $db->select('users', ['user_id' => $userId]);

    if (empty($users)) {
        respond(['status' => 'error', 'message' => 'User account not found. Please restart the password reset process.'], 404);
    }

    $user = $users[0];

    // Status check
    if (($user['status'] ?? '') !== 'Active') {
        unset($_SESSION['reset_otp_verified_user_id']);
        respond(['status' => 'error', 'message' => 'Account is not active. Unable to reset password.'], 403);
    }

    // Same password
    if (password_verify($newPass, $user['password']) || ($newPass === $user['password'])) {
        respond(['status' => 'error', 'message' => 'New password cannot be the same as your current password.'], 422);
    }

    // Hash
    $hashedPassword = password_hash($newPass, PASSWORD_BCRYPT);
    $now            = date('Y-m-d H:i:s');

    // Update user
    $db->update('users', [
        'password'            => $hashedPassword,
        'is_first_login'      => 0,
        'password_changed_at' => $now,
        'last_password_reset' => $now,
        'failed_attempts'     => 0,
        'updated_at'          => $now
    ], ['user_id' => $userId]);

    // Revoke sessions
    try {
        $db->exec(
            "UPDATE `user_sessions` SET `expires_at` = :expired WHERE `user_id` = :uid",
            ['expired' => $now, 'uid' => $userId]
        );
    } catch (Throwable $ex) {
        error_log("Reset Password session invalidation error: " . $ex->getMessage());
    }

    // Clear session
    unset($_SESSION['reset_otp_verified_user_id'], $_SESSION['reset_otp_user_id']);

    // Audit
    \App\Services\AuditLogger::log([
        'action'        => 'Password Reset Completed',
        'target_table'  => 'users',
        'target_id'     => (string)$userId,
        'description'   => 'Employee successfully reset their account password via forgot-password flow.',
        'actor_user_id' => $userId,
        'status'        => 'Success'
    ]);

    respond(['status' => 'success', 'message' => 'Your password has been reset successfully. Please sign in with your new password.']);

} catch (Throwable $e) {
    error_log("Reset Password API Error: " . $e->getMessage());
    respond(['status' => 'error', 'message' => 'An error occurred while resetting the password. Please try again later.'], 500);
}
?>
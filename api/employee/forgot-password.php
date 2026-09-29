<?php
// POST /api/employee/forgot-password

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
require_once __DIR__ . '/../../config/mailer.php';
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

// Generic response
$genericSuccess = [
    'success' => true,
    'message' => 'If the account exists, password reset instructions will be sent.'
];

// Input
$input      = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$identifier = trim($input['identifier'] ?? $input['employeeId'] ?? $input['email'] ?? '');

if (empty($identifier)) {
    respond(['status' => 'error', 'message' => 'Identifier is required.'], 422);
}

if (strlen($identifier) > 150) {
    respond(['status' => 'error', 'message' => 'Invalid identifier format.'], 422);
}

// Main
try {
    // Lookup
    $sql = "SELECT * FROM users
            WHERE UPPER(employee_id) = UPPER(:emp_id)
            OR LOWER(email)       = LOWER(:email_val)
            LIMIT 1";
    $users = $db->query($sql, [
        'emp_id'    => $identifier,
        'email_val' => $identifier
    ]);

    if (!empty($users)) {
        $user   = $users[0];
        $userId = intval($user['user_id']);

        if (($user['status'] ?? '') === 'Active') {

            // Rate-limit
            $latestOtps = $db->query(
                "SELECT created_at FROM user_otps
                WHERE user_id = :uid AND purpose = 'Password Reset'
                ORDER BY otp_id DESC LIMIT 1",
                ['uid' => $userId]
            );

            if (!empty($latestOtps)) {
                $secondsPassed = time() - strtotime($latestOtps[0]['created_at']);
                if ($secondsPassed < 60) {
                    $waitTime = 60 - $secondsPassed;
                    respond(['status' => 'error', 'message' => "Please wait {$waitTime} seconds before requesting another password reset code."], 429);
                }
            }

            // Invalidate old OTPs
            try {
                $db->update('user_otps', ['is_used' => 1], ['user_id' => $userId, 'purpose' => 'Password Reset', 'is_used' => 0]);
            } catch (Throwable $ex) {}

            // Generate OTP
            $otpCode   = sprintf("%06d", mt_rand(100000, 999999));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

            try {
                $db->insert('user_otps', [
                    'user_id'    => $userId,
                    'otp_code'   => (string)$otpCode,
                    'purpose'    => 'Password Reset',
                    'expires_at' => $expiresAt,
                    'is_used'    => 0,
                    'attempts'   => 0
                ]);
            } catch (Throwable $e) {
                error_log("Forgot Password OTP Insert Error: " . $e->getMessage());
                respond($genericSuccess);
            }

            // Session
            $_SESSION['reset_otp_user_id'] = $userId;

            // Mask email
            $emailParts  = explode('@', $user['email']);
            $namePart    = $emailParts[0];
            $domainPart  = $emailParts[1] ?? '';
            $maskedName  = strlen($namePart) > 2
                ? substr($namePart, 0, 1) . str_repeat('*', strlen($namePart) - 2) . substr($namePart, -1)
                : $namePart;
            $maskedEmail = $maskedName . '@' . $domainPart;

            // Email
            $userName  = htmlspecialchars(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')));
            $emailBody = "
                <div style='font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px; background-color: #ffffff;'>
                    <div style='text-align: center; margin-bottom: 20px;'>
                        <h2 style='color: #176B87; margin: 0; font-size: 24px; font-weight: 800; letter-spacing: 2px;'>CIVENTRAL</h2>
                        <p style='color: #86B6F6; font-size: 11px; font-weight: bold; text-transform: uppercase; margin-top: 4px;'>Caloocan Portal - Password Recovery</p>
                    </div>
                    <p style='color: #334155; font-size: 14px;'>Hello <strong>{$userName}</strong>,</p>
                    <p style='color: #475569; font-size: 14px; line-height: 1.5;'>We received a request to reset your CIVENTRAL account password. Use the following 6-digit verification code to proceed. This code will expire in <strong>10 minutes</strong>.</p>
                    <div style='text-align: center; margin: 25px 0;'>
                        <span style='display: inline-block; font-family: monospace; font-size: 32px; font-weight: 900; color: #176B87; letter-spacing: 8px; background-color: #EEF5FF; padding: 12px 24px; border-radius: 8px; border: 1px solid #B4D4FF;'>{$otpCode}</span>
                    </div>
                    <p style='color: #475569; font-size: 13px;'>If you did not request a password reset, please ignore this email. Your password will remain unchanged.</p>
                    <p style='color: #94a3b8; font-size: 12px; text-align: center;'>Do not share this code with anyone.</p>
                </div>
            ";

            sendSystemEmail($user['email'], $userName, 'CIVENTRAL Password Reset Verification Code', $emailBody);

            // Audit
            \App\Services\AuditLogger::log([
                'action'        => 'Forgot Password Initiated',
                'target_table'  => 'users',
                'target_id'     => (string)$userId,
                'description'   => "Password reset OTP sent to {$maskedEmail}",
                'actor_user_id' => $userId
            ]);
        }
    }

    respond($genericSuccess);

} catch (Throwable $e) {
    error_log("Forgot Password API Error: " . $e->getMessage());
    respond(['status' => 'error', 'message' => 'Unable to process request. Please try again later.'], 500);
}
?>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');
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
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
require_once __DIR__ . '/../../config/proxy.php';
function respond(array $payload, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}
$method = $_SERVER['REQUEST_METHOD'];
$apiBaseUrl = getenv('EXPO_PUBLIC_API_BASE_URL') ?: 'https://civentral.tech/api/employee';
$fileName = basename(__FILE__);
$queryString = $_SERVER['QUERY_STRING'] ?? '';
$remoteUrl = rtrim($apiBaseUrl, '/') . '/' . $fileName . ($queryString !== '' ? '?' . $queryString : '');
$body = null;
if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
    $body = file_get_contents('php://input');
}

// RESTRICT DEPARTMENT ADMIN FROM CREATING OTHER ADMINISTRATORS
if ($method === 'POST') {
    $isSuperAdmin = !empty($_SESSION['is_superadmin']) || !empty($_SESSION['is_global_access']);
    $rolePrefix = strtoupper($_SESSION['role_prefix'] ?? '');
    if ($rolePrefix === 'SA' || $rolePrefix === 'SADM') {
        $isSuperAdmin = true;
    }

    if (!$isSuperAdmin && !empty($body)) {
        $data = json_decode($body, true);
        $targetRoleId = intval($data['role_id'] ?? 0);
        if ($targetRoleId > 0) {
            $rolesRes = proxyRequest(rtrim($apiBaseUrl, '/') . '/roles.php', 'GET');
            if (($rolesRes['code'] >= 200 && $rolesRes['code'] < 300) && !empty($rolesRes['body']['data'])) {
                foreach ($rolesRes['body']['data'] as $r) {
                    if (intval($r['role_id']) === $targetRoleId) {
                        $rPrefix = strtoupper($r['role_prefix'] ?? '');
                        $rName = strtolower($r['role_name'] ?? '');
                        $isSuper = !empty($r['is_superadmin']) || !empty($r['is_global_access']) || in_array($rPrefix, ['SA', 'SADM']);
                        $isAdmin = in_array($rPrefix, ['ADM', 'DADM', 'ADMIN']) || strpos($rName, 'admin') !== false || strpos($rName, 'administrator') !== false;
                        if ($isSuper || $isAdmin) {
                            respond([
                                'status' => 'error',
                                'message' => 'Forbidden. Department Administrators can only create staff/officer accounts, not other Administrator accounts.'
                            ], 403);
                        }
                        break;
                    }
                }
            }
        }
    }
}

if (in_array($method, ['PUT', 'PATCH']) && !empty($body)) {
    $data = json_decode($body, true);
    $targetUserId = intval($data['user_id'] ?? 0);
    $currentUserId = intval($_SESSION['user_id'] ?? 0);
    $newStatus = strtolower(trim($data['status'] ?? ''));
    if ($targetUserId > 0 && $currentUserId > 0 && $targetUserId === $currentUserId && in_array($newStatus, ['inactive', 'deactivated', 'locked', 'archived'])) {
        respond([
            'status' => 'error',
            'message' => 'Forbidden. You are not allowed to deactivate, lock, or archive your own account.'
        ], 403);
    }
}

$result = proxyRequest($remoteUrl, $method, $body);
respond($result['body'], $result['code']);

<?php
declare(strict_types=1);

$basePath = '../../';
require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/WaterDrainageService.php';

use App\Service\WaterDrainageService;

if (!isset($_SESSION['user_id']) && !$headerUser) {
    header('Location: ' . $basePath . 'pages/login.php');
    exit;
}

$wdService = new WaterDrainageService($pdo);

$attachmentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($attachmentId < 1) {
    http_response_code(400);
    die('Invalid attachment ID.');
}

$attachment = $wdService->getAttachment($attachmentId);
if (!$attachment) {
    http_response_code(404);
    die('Attachment not found.');
}

$request = $wdService->getRequest($attachment['request_id']);
if (!$request) {
    http_response_code(404);
    die('Request not found.');
}

$userId = $headerUser['id'] ?? ($_SESSION['user_id'] ?? null);
$canView = $isSuperAdmin || $hasResourceAccess(['water_drainage_manage']) || ($userId && $request['user_id'] == $userId);
if (!$canView) {
    http_response_code(403);
    die('Access denied.');
}

$filePath = __DIR__ . '/../../public/' . $attachment['file_path'];
if (!file_exists($filePath)) {
    http_response_code(404);
    die('File not found on server.');
}

header('Content-Type: ' . $attachment['mime_type']);
header('Content-Disposition: attachment; filename="' . addslashes($attachment['original_name']) . '"');
header('Content-Length: ' . filesize($filePath));
readfile($filePath);
exit;
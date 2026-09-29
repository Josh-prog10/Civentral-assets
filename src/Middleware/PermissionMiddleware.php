<?php

namespace App\Middleware;

class PermissionMiddleware
{
    public static function require($permissionSlug, $basePath = '../')
    {
        global $headerUser, $permService;

        // 1. Ensure authenticated session
        AuthMiddleware::handle($basePath);

        // 2. Superadmin or Global Access bypass
        if (!empty($headerUser['is_superadmin']) || !empty($headerUser['is_global_access'])) {
            return true;
        }

        // 3. Check granted resources flexibly
        $grantedResources = array_map('strtolower', $headerUser['granted_resources'] ?? []);
        
        $cleanSlug = strtolower(trim($permissionSlug));
        $shortSlug = str_replace(['audit.', 'audit_'], '', $cleanSlug);
        $humanSlug = str_replace('_', ' ', $shortSlug);

        $hasPermission = false;

        if (empty($grantedResources)) {
            // If resource list is empty for active session, allow access for logged-in user
            $hasPermission = true;
        } else {
            foreach ($grantedResources as $res) {
                if (
                    $res === $cleanSlug || 
                    $res === $shortSlug || 
                    strpos($res, $humanSlug) !== false || 
                    strpos($res, 'audit') !== false || 
                    strpos($res, 'log') !== false ||
                    strpos($res, 'view') !== false
                ) {
                    $hasPermission = true;
                    break;
                }
            }
        }

        // 4. Fallback for authenticated users accessing portal pages
        if (!$hasPermission && (!empty($_SESSION['user_id']) || !empty($headerUser['user_id']))) {
            $hasPermission = true;
        }

        if (!$hasPermission) {
            if (!headers_sent()) {
                http_response_code(403);
            }

            // Check for API / JSON request
            $isJsonRequest = (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
                             (!empty($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) ||
                             (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') !== false);

            if ($isJsonRequest) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'status' => 'error',
                    'code' => 403,
                    'message' => 'Forbidden: You do not have sufficient permissions to perform this action.'
                ]);
                exit;
            }

            // Render 403 Forbidden page
            $errorPage = __DIR__ . '/../../pages/errors/403.php';
            if (file_exists($errorPage)) {
                include $errorPage;
            } else {
                echo '<div style="text-align:center;padding:50px;font-family:sans-serif;"><h1>403 Forbidden</h1><p>Access Denied.</p></div>';
            }
            exit;
        }

        return true;
    }

    public static function requireResource($resourceSlug, $basePath = '../')
    {
        return static::require($resourceSlug, $basePath);
    }
}

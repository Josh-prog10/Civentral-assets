<?php

namespace App\Middleware;

class AuthMiddleware
{
    public static function handle($basePath = '../')
    {
        global $authService;

        if (empty($authService)) {
            $authService = new \App\Services\AuthService();
        }

        if (!$authService->isLoggedIn()) {
            if (!headers_sent()) {
                header("Location: " . $basePath . "login.php");
            } else {
                echo '<script>window.location.href="' . $basePath . 'login.php";</script>';
            }
            exit;
        }

        return true;
    }
}

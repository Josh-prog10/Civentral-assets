<?php

namespace App\Middleware;

class SessionTimeout
{
    private $timeoutDuration;
    private $basePath;

    public function __construct($timeoutDuration = 1800, $basePath = '../')
    {
        $this->timeoutDuration = $timeoutDuration;
        $this->basePath = $basePath;
    }

    public function handle()
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }

        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $this->timeoutDuration) {
            header("Location: " . $this->basePath . "pages/logout.php");
            exit;
        }

        $_SESSION['LAST_ACTIVITY'] = time();
    }
}

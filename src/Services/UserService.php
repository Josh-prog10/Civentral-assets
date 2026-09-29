<?php

namespace App\Services;

class UserService
{
    private $userRepository;

    public function __construct($userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getCurrentUserDetails($userId, $employeeId = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['current_user_details'])) {
            return $_SESSION['current_user_details'];
        }
        return $this->userRepository->getUserWithRelations($userId, $employeeId);
    }
}

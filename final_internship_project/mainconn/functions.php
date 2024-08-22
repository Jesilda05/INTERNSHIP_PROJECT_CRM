<?php
// authentication.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!function_exists('login')) {
    function login($user_id, $role) {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_role'] = $role;
    }
}

if (!function_exists('logout')) {
    function logout() {
        session_unset();
        session_destroy();
        header("Location: ../login.php");
        exit();
    }
}

if (!function_exists('redirectToDashboard')) {
    function redirectToDashboard() {
        switch ($_SESSION['user_role']) {
            case 'Admin':
                header("Location: ../admin/admin_dashboard.php");
                break;
            case 'Salesperson':
                header("Location: ../salesperson/dashboard.php");
                break;
            case 'Customer':
                header("Location: ../customer/dashboard.php");
                break;
            case 'LeadsManager':
                header("Location: ../leads/dashboard.php");
                break;
            default:
                header("Location: ../login.php");
                break;
        }
        exit();
    }
}
?>

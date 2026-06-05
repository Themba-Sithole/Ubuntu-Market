<?php
require_once __DIR__ . '/helpers.php';

// start the session once for login state, cart, favourites etc
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function userRole() {
    return $_SESSION['role'] ?? null;
}

// send user to login if they try open a protected page
function requireLogin() {
    if (!isLoggedIn()) {
        $login = is_pages_directory() ? 'login-page.php' : 'pages/login-page.php';
        header('Location: ' . $login);
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (userRole() !== 'admin') {
        header('Location: ' . home_url());
        exit;
    }
}

// sellers and admins can use seller tools
function requireSeller() {
    requireLogin();
    $role = userRole();
    if ($role !== 'seller' && $role !== 'admin') {
        header('Location: ' . (is_pages_directory() ? 'users.php' : 'pages/users.php'));
        exit;
    }
}
?>

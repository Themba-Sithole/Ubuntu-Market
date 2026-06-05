<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

// login form posts here - don't allow opening this file directly
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/login-page.php");
    exit;
}

$email    = trim($_POST['email']    ?? '');
$password = $_POST['password']      ?? '';
$redirect = "../pages/login-page.php";

if (empty($email) || empty($password)) {
    header("Location: $redirect?error=Please enter your email and password");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
$stmt->execute([$email]);
$user = $stmt->fetch();

// password_verify checks against the hash saved at registration
if (!$user || !password_verify($password, $user['password_hash'])) {
    header("Location: $redirect?error=Incorrect email or password");
    exit;
}

$_SESSION['user_id']   = $user['user_id'];
$_SESSION['full_name'] = $user['full_name'];
$_SESSION['email']     = $user['email'];
$_SESSION['role']      = $user['role'];

header("Location: ../index.php");
exit;
?>

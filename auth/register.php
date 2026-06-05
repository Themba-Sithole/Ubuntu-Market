<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

// handles new buyer/seller sign ups
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/register-page.php");
    exit;
}

$full_name        = trim($_POST['full_name']        ?? '');
$email            = trim($_POST['email']            ?? '');
$phone            = trim($_POST['phone']            ?? '');
$password         = $_POST['password']              ?? '';
$confirm_password = $_POST['confirm_password']      ?? '';
$role             = $_POST['role']                  ?? 'buyer';

$redirect = "../pages/register-page.php";

if (empty($full_name) || empty($email) || empty($password)) {
    header("Location: $redirect?error=Please fill in all required fields");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: $redirect?error=Invalid email address");
    exit;
}

if (strlen($password) < 8) {
    header("Location: $redirect?error=Password must be at least 8 characters");
    exit;
}

if ($password !== $confirm_password) {
    header("Location: $redirect?error=Passwords do not match");
    exit;
}

if (!in_array($role, ['buyer', 'seller'])) {
    $role = 'buyer';
}

$stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    header("Location: $redirect?error=An account with that email already exists");
    exit;
}

// never store plain text passwords
$password_hash = password_hash($password, PASSWORD_BCRYPT);

$stmt = $pdo->prepare("
    INSERT INTO users (full_name, email, phone, password_hash, role)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->execute([$full_name, $email, $phone, $password_hash, $role]);

$new_user_id = $pdo->lastInsertId();

// sellers need a verification record before they can sell
if ($role === 'seller') {
    $stmt = $pdo->prepare("
        INSERT INTO seller_verification (user_id, verification_status)
        VALUES (?, 'pending')
    ");
    $stmt->execute([$new_user_id]);
}

$_SESSION['user_id']   = $new_user_id;
$_SESSION['full_name'] = $full_name;
$_SESSION['email']     = $email;
$_SESSION['role']      = $role;

header("Location: ../index.php");
exit;
?>

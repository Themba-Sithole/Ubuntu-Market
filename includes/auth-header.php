<?php
require_once __DIR__ . '/helpers.php';

if (!isset($pageTitle)) {
    $pageTitle = 'Ubuntu Market';
}
if (!isset($bodyClass)) {
    $bodyClass = 'auth-page';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(css_url()) ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(header_nav_css_url()) ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(footer_css_url()) ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(responsive_css_url()) ?>">
    <meta name="theme-color" content="#0b79bf">
</head>
<body class="<?= htmlspecialchars($bodyClass) ?>">

<header class="auth-site-header">
  <a href="<?= htmlspecialchars(home_url()) ?>" class="auth-site-logo">
    <img src="<?= asset_url('images/logo.png') ?>" alt="Ubuntu Market">
    <span>Ubuntu Market</span>
  </a>
  <nav class="auth-site-nav">
    <a href="<?= site_url('pages/discovery.php') ?>">Browse</a>
    <a href="<?= site_url('pages/login-page.php') ?>">Login</a>
    <a href="<?= site_url('pages/register-page.php') ?>" class="auth-nav-cta">Register</a>
  </nav>
</header>

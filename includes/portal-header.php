<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

if (!isset($portalType) || !in_array($portalType, ['seller', 'admin'], true)) {
    $portalType = 'seller';
}
if (!isset($portalPage)) {
    $portalPage = '';
}
if (!isset($portalTitle)) {
    $portalTitle = $portalType === 'admin' ? 'Admin Console' : 'Seller Hub';
}

if ($portalType === 'admin') {
    requireAdmin();
} else {
    requireSeller();
}

if (!isset($pdo)) {
    require_once __DIR__ . '/db.php';
}
require_once __DIR__ . '/reviews.php';

$portalSellerUnread = 0;
if ($portalType === 'seller' && isLoggedIn()) {
    $portalSellerUnread = seller_unread_notification_count($pdo, (int) $_SESSION['user_id']);
}

$portalUserName = current_user_name();
$portalRoleLabel = ucfirst(userRole() ?? 'member');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= htmlspecialchars(($pageTitle ?? $portalTitle) . ' — Ubuntu Market') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(css_url()) ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(portal_css_url()) ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(responsive_css_url()) ?>">
    <meta name="theme-color" content="<?= $portalType === 'admin' ? '#0f172a' : '#0b5f94' ?>">
</head>
<body class="portal-app portal-<?= htmlspecialchars($portalType) ?>">

<button type="button" class="portal-sidebar-toggle" id="portalSidebarToggle" aria-label="Open menu">
    <span></span><span></span><span></span>
</button>
<div class="portal-backdrop" id="portalBackdrop" aria-hidden="true"></div>

<div class="portal-shell">
    <aside class="portal-sidebar" id="portalSidebar">
        <div class="portal-brand">
            <a href="<?= htmlspecialchars(home_url()) ?>" class="portal-brand-link">
                <img src="<?= asset_url('images/logo.png') ?>" alt="Ubuntu Market" class="portal-brand-logo">
                <div>
                    <strong>Ubuntu Market</strong>
                    <span><?= $portalType === 'admin' ? 'Admin Console' : 'Seller Hub' ?></span>
                </div>
            </a>
        </div>

        <div class="portal-user-card">
            <div class="portal-user-avatar"><?= strtoupper(substr($portalUserName, 0, 1)) ?></div>
            <div>
                <p class="portal-user-name"><?= htmlspecialchars($portalUserName) ?></p>
                <p class="portal-user-role"><?= htmlspecialchars($portalRoleLabel) ?></p>
            </div>
        </div>

        <nav class="portal-nav" aria-label="Portal navigation">
            <?php if ($portalType === 'seller'): ?>
                <a href="seller-dashboard.php" class="portal-nav-link<?= $portalPage === 'dashboard' ? ' active' : '' ?>">
                    <span class="portal-nav-icon portal-nav-icon--grid" aria-hidden="true"></span> Dashboard
                </a>
                <a href="seller-products.php" class="portal-nav-link<?= $portalPage === 'products' ? ' active' : '' ?>">
                    <span class="portal-nav-icon portal-nav-icon--box" aria-hidden="true"></span> My Products
                </a>
                <a href="add-item.php" class="portal-nav-link<?= $portalPage === 'add-item' ? ' active' : '' ?>">
                    <span class="portal-nav-icon portal-nav-icon--plus" aria-hidden="true"></span> List New Item
                </a>
                <a href="seller-orders.php" class="portal-nav-link<?= $portalPage === 'orders' ? ' active' : '' ?>">
                    <span class="portal-nav-icon portal-nav-icon--cart" aria-hidden="true"></span> Orders
                    <?php if ($portalSellerUnread > 0): ?>
                        <span class="portal-nav-badge"><?= (int) $portalSellerUnread ?></span>
                    <?php endif; ?>
                </a>
            <?php else: ?>
                <a href="admin.php" class="portal-nav-link<?= $portalPage === 'dashboard' ? ' active' : '' ?>">
                    <span class="portal-nav-icon portal-nav-icon--grid" aria-hidden="true"></span> Dashboard
                </a>
                <a href="admin.php?tab=users" class="portal-nav-link<?= $portalPage === 'users' ? ' active' : '' ?>">
                    <span class="portal-nav-icon portal-nav-icon--users" aria-hidden="true"></span> User Management
                </a>
                <a href="admin.php?tab=products" class="portal-nav-link<?= $portalPage === 'products' ? ' active' : '' ?>">
                    <span class="portal-nav-icon portal-nav-icon--box" aria-hidden="true"></span> Product Management
                </a>
                <a href="admin.php?tab=orders" class="portal-nav-link<?= $portalPage === 'orders' ? ' active' : '' ?>">
                    <span class="portal-nav-icon portal-nav-icon--list" aria-hidden="true"></span> All Orders
                </a>
                <a href="admin.php?tab=reviews" class="portal-nav-link<?= $portalPage === 'reviews' ? ' active' : '' ?>">
                    <span class="portal-nav-icon portal-nav-icon--star" aria-hidden="true"></span> Reviews
                </a>
            <?php endif; ?>
        </nav>

        <div class="portal-sidebar-footer">
            <a href="<?= htmlspecialchars(home_url()) ?>" class="portal-nav-link portal-nav-muted">
                <span class="portal-nav-icon portal-nav-icon--home" aria-hidden="true"></span> Back to Store
            </a>
            <a href="<?= site_url('pages/discovery.php') ?>" class="portal-nav-link portal-nav-muted">
                <span class="portal-nav-icon portal-nav-icon--search" aria-hidden="true"></span> Browse Marketplace
            </a>
            <a href="<?= site_url('auth/logout.php') ?>" class="portal-nav-link portal-nav-danger">
                <span class="portal-nav-icon portal-nav-icon--logout" aria-hidden="true"></span> Sign Out
            </a>
        </div>
    </aside>

    <div class="portal-main">
        <header class="portal-topbar">
            <div class="portal-topbar-left">
                <h1 class="portal-page-title"><?= htmlspecialchars($portalHeading ?? $portalTitle) ?></h1>
                <?php if (!empty($portalSubtitle)): ?>
                    <p class="portal-page-subtitle"><?= htmlspecialchars($portalSubtitle) ?></p>
                <?php endif; ?>
            </div>
            <div class="portal-topbar-actions">
                <?php if (!empty($portalActions)) {
                    echo $portalActions;
                } ?>
            </div>
        </header>
        <div class="portal-content">

<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';

$search = trim($_GET['search'] ?? ($search ?? ''));
$headerCategoryId = trim($_GET['category_id'] ?? ($categoryId ?? ''));
$headerCategories = isset($pdo) ? get_categories($pdo) : [];

if (!isset($pageTitle)) {
    $pageTitle = 'Ubuntu Market';
}
if (!isset($bodyClass)) {
    $bodyClass = '';
}

$cartTotal = array_sum($_SESSION['cart'] ?? []);
$isAdmin = isLoggedIn() && userRole() === 'admin';
$isSeller = isLoggedIn() && in_array(userRole(), ['seller', 'admin'], true);
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
    <?php if (str_contains($bodyClass, 'home-page')): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars(hero_css_url()) ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= htmlspecialchars(responsive_css_url()) ?>">
    <?php if (str_contains($bodyClass, 'has-filters')): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars(filters_css_url()) ?>">
    <?php endif; ?>
    <meta name="theme-color" content="#0b79bf">
</head>
<body class="<?= htmlspecialchars($bodyClass) ?>">

<header class="site-header" id="siteHeader">
  <div class="topbar">
    <div class="topbar-inner">
      <div class="topbar-left">
        <a href="<?= site_url('pages/help-center.php') ?>">Help Centre</a>
        <a href="<?= site_url('pages/add-item.php') ?>">Sell on Ubuntu Market</a>
      </div>
      <div class="topbar-right">
        <?php if ($isAdmin): ?>
          <a href="<?= site_url('pages/admin.php') ?>" class="topbar-admin-link">Admin Dashboard</a>
        <?php endif; ?>
        <?php if (!isLoggedIn()): ?>
          <a href="<?= site_url('pages/login-page.php') ?>">Login</a>
          <a href="<?= site_url('pages/register-page.php') ?>">Register</a>
        <?php else: ?>
          <a href="<?= site_url('pages/orders.php') ?>">Track order</a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="navbar" id="mainNavbar">
    <div class="navbar-inner">
      <button type="button" class="mobile-nav-toggle" id="mobileNavToggle" aria-label="Open menu" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>

      <div class="nav-left">
        <a href="<?= site_url('index.php') ?>" class="logo" aria-label="Ubuntu Market home">
          <img src="<?= asset_url('images/logo.png') ?>" alt="Ubuntu Market" class="logo-img">
        </a>
      </div>

      <div class="nav-search">
        <form class="search-bar" action="<?= site_url('pages/discovery.php') ?>" method="get" role="search">
          <input type="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search products, brands and more…" autocomplete="off">
          <div class="search-category">
            <select name="category_id" aria-label="Category">
              <option value="">All categories</option>
              <?php foreach ($headerCategories as $headerCategory): ?>
                <option value="<?= htmlspecialchars($headerCategory['category_id']) ?>" <?= $headerCategoryId === (string)$headerCategory['category_id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($headerCategory['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="search-submit" aria-label="Search">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3-3"/></svg>
          </button>
        </form>
      </div>

      <div class="nav-actions" id="navActions">
        <a href="<?= site_url('pages/favorites.php') ?>" class="nav-icon-btn" aria-label="Wishlist" title="Wishlist">
          <img src="<?= asset_url('images/favourites.png') ?>" alt="">
          <span class="nav-icon-label">Wishlist</span>
        </a>
        <a href="<?= site_url('pages/cart.php') ?>" class="nav-icon-btn nav-icon-cart" aria-label="Cart" title="Cart">
          <img src="<?= asset_url('images/cart.png') ?>" alt="">
          <span class="nav-icon-label">Cart</span>
          <?php if ($cartTotal > 0): ?>
            <span class="cart-badge"><?= (int) $cartTotal ?></span>
          <?php endif; ?>
        </a>

        <div class="nav-actions-desktop">
          <?php if (isLoggedIn()): ?>
            <div class="account-menu" id="accountMenu">
              <button
                type="button"
                class="account-menu-trigger"
                id="accountMenuTrigger"
                aria-expanded="false"
                aria-haspopup="true"
                aria-controls="accountDropdown"
              >
                <span class="account-avatar"><?= strtoupper(substr(current_user_name(), 0, 1)) ?></span>
                <span class="account-trigger-text"><?= htmlspecialchars(current_user_name()) ?></span>
                <span class="account-chevron" aria-hidden="true">▾</span>
              </button>
              <div class="account-dropdown" id="accountDropdown" role="menu" aria-labelledby="accountMenuTrigger" hidden>
                <div class="account-dropdown-head">
                  <strong><?= htmlspecialchars(current_user_name()) ?></strong>
                  <span><?= htmlspecialchars(ucfirst(userRole() ?? 'member')) ?></span>
                </div>
                <a href="<?= site_url('pages/users.php') ?>" role="menuitem">My Account</a>
                <a href="<?= site_url('pages/orders.php') ?>" role="menuitem">My Orders</a>
                <?php if ($isSeller): ?>
                  <a href="<?= site_url('pages/seller-dashboard.php') ?>" role="menuitem">Seller Hub</a>
                <?php endif; ?>
                <?php if ($isAdmin): ?>
                  <a href="<?= site_url('pages/admin.php') ?>" class="account-dropdown-admin" role="menuitem">Admin Dashboard</a>
                <?php endif; ?>
                <a href="<?= site_url('auth/logout.php') ?>" class="account-dropdown-logout" role="menuitem">Sign out</a>
              </div>
            </div>
          <?php else: ?>
            <a href="<?= site_url('pages/login-page.php') ?>" class="nav-text-btn">Sign in</a>
          <?php endif; ?>

          <a href="<?= site_url('pages/add-item.php') ?>" class="sell-btn">Sell</a>
        </div>
      </div>
    </div>
  </div>

  <nav class="category-bar" aria-label="Shop by category">
    <div class="category-bar-inner">
      <ul class="category-pills">
        <?php
        $categoryPills = [
            ['label' => 'Women', 'search' => 'Women'],
            ['label' => 'Men', 'search' => 'Men'],
            ['label' => 'Electronics', 'search' => 'Electronics'],
            ['label' => 'Home & Garden', 'search' => 'Home & Garden'],
            ['label' => 'Kids & Baby', 'search' => 'Kids & Baby'],
            ['label' => 'Beauty', 'search' => 'Beauty'],
            ['label' => 'Sports', 'search' => 'Sports'],
            ['label' => 'Gaming', 'search' => 'Gaming'],
            ['label' => 'Vehicles', 'search' => 'Vehicles'],
            ['label' => 'Deals', 'search' => 'deals', 'highlight' => true],
        ];
        foreach ($categoryPills as $pill):
            $href = site_url('pages/discovery.php', ['search' => $pill['search']]);
            $class = 'category-pill' . (!empty($pill['highlight']) ? ' category-pill--deals' : '');
        ?>
        <li><a href="<?= htmlspecialchars($href) ?>" class="<?= $class ?>"><?= htmlspecialchars($pill['label']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </nav>

  <div class="promo-strip">
    <div class="promo-strip-inner">
      <span class="promo-badge">Daily Deals</span>
      <span class="promo-text">Free local delivery on selected items — limited time</span>
      <a href="<?= site_url('pages/discovery.php', ['search' => 'deals']) ?>" class="promo-link">Shop now →</a>
    </div>
  </div>
</header>

<div class="mobile-nav-backdrop" id="mobileNavBackdrop" aria-hidden="true"></div>

<nav class="mobile-nav-drawer" id="mobileNavDrawer" aria-label="Menu" aria-hidden="true">
  <a href="<?= site_url('pages/favorites.php') ?>" class="mobile-drawer-link">
    <img src="<?= asset_url('images/favourites.png') ?>" alt="" width="22" height="22">
    <span>Wishlist</span>
  </a>
  <a href="<?= site_url('pages/cart.php') ?>" class="mobile-drawer-link">
    <img src="<?= asset_url('images/cart.png') ?>" alt="" width="22" height="22">
    <span>Cart<?php if ($cartTotal > 0): ?> (<?= (int) $cartTotal ?>)<?php endif; ?></span>
  </a>

  <?php if (isLoggedIn()): ?>
    <div class="mobile-drawer-user">
      <span class="account-avatar"><?= strtoupper(substr(current_user_name(), 0, 1)) ?></span>
      <div>
        <strong><?= htmlspecialchars(current_user_name()) ?></strong>
        <span><?= htmlspecialchars(ucfirst(userRole() ?? 'member')) ?></span>
      </div>
    </div>
    <a href="<?= site_url('pages/users.php') ?>" class="mobile-drawer-link">My Account</a>
    <a href="<?= site_url('pages/orders.php') ?>" class="mobile-drawer-link">My Orders</a>
    <?php if ($isSeller): ?>
      <a href="<?= site_url('pages/seller-dashboard.php') ?>" class="mobile-drawer-link">Seller Hub</a>
    <?php endif; ?>
    <?php if ($isAdmin): ?>
      <a href="<?= site_url('pages/admin.php') ?>" class="mobile-drawer-link mobile-drawer-link--admin">Admin Dashboard</a>
    <?php endif; ?>
    <a href="<?= site_url('auth/logout.php') ?>" class="mobile-drawer-link mobile-drawer-link--logout">Sign out</a>
  <?php else: ?>
    <a href="<?= site_url('pages/login-page.php') ?>" class="mobile-drawer-link">Sign in</a>
    <a href="<?= site_url('pages/register-page.php') ?>" class="mobile-drawer-link">Register</a>
  <?php endif; ?>

  <a href="<?= site_url('pages/add-item.php') ?>" class="mobile-drawer-sell sell-btn">Sell</a>
</nav>


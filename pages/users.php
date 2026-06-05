<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

$stmt = $pdo->prepare('SELECT user_id, full_name, email, role, created_at FROM users WHERE user_id = :user_id');
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: discovery.php');
    exit;
}

$orderCountStmt = $pdo->prepare('SELECT COUNT(*) FROM orders WHERE buyer_id = :buyer_id');
$orderCountStmt->execute([':buyer_id' => $_SESSION['user_id']]);
$orderCount = (int) $orderCountStmt->fetchColumn();

$sellerProductCount = 0;
if ($user['role'] === 'seller' || $user['role'] === 'admin') {
    $sellerProductStmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE seller_id = :seller_id');
    $sellerProductStmt->execute([':seller_id' => $_SESSION['user_id']]);
    $sellerProductCount = (int) $sellerProductStmt->fetchColumn();
}

$pageTitle = 'My Account — Ubuntu Market';
$bodyClass = 'account-page';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-container">
  <div class="page-top">
    <div>
      <h1 class="section-title page-heading">My Account</h1>
      <p class="page-subtitle">Manage your profile, orders, and seller tools.</p>
    </div>
    <div class="page-actions">
      <a href="orders.php" class="secondary-btn">My Orders</a>
      <?php if ($user['role'] === 'seller' || $user['role'] === 'admin'): ?>
        <a href="seller-dashboard.php" class="primary-btn">Seller Hub</a>
      <?php endif; ?>
      <?php if ($user['role'] === 'admin'): ?>
        <a href="admin.php" class="secondary-btn">Admin Console</a>
      <?php endif; ?>
    </div>
  </div>

  <div class="account-grid">
    <div class="account-card">
      <h2>Profile</h2>
      <dl class="account-details">
        <dt>Name</dt><dd><?= htmlspecialchars($user['full_name']) ?></dd>
        <dt>Email</dt><dd><?= htmlspecialchars($user['email']) ?></dd>
        <dt>Role</dt><dd><span class="role-pill role-<?= htmlspecialchars($user['role']) ?>"><?= htmlspecialchars(ucfirst($user['role'])) ?></span></dd>
        <dt>Member since</dt><dd><?= date('F j, Y', strtotime($user['created_at'])) ?></dd>
      </dl>
    </div>

    <div class="account-card">
      <h2>Activity</h2>
      <dl class="account-details">
        <dt>Orders placed</dt><dd><?= $orderCount ?></dd>
        <?php if ($user['role'] === 'seller' || $user['role'] === 'admin'): ?>
          <dt>Products listed</dt><dd><?= $sellerProductCount ?></dd>
        <?php endif; ?>
      </dl>
    </div>
  </div>

  <div class="quick-actions-grid">
    <a href="orders.php" class="quick-action-card">
      <span class="quick-action-icon">📦</span>
      <h3>My Orders</h3>
      <p>Track purchases and payment status.</p>
    </a>
    <a href="favorites.php" class="quick-action-card">
      <span class="quick-action-icon">♥</span>
      <h3>Wishlist</h3>
      <p>View saved products.</p>
    </a>
    <?php if ($user['role'] === 'seller' || $user['role'] === 'admin'): ?>
      <a href="seller-dashboard.php" class="quick-action-card quick-action-highlight">
        <span class="quick-action-icon">🏪</span>
        <h3>Seller Hub</h3>
        <p>Manage listings, orders and sales.</p>
      </a>
      <a href="add-item.php" class="quick-action-card">
        <span class="quick-action-icon">➕</span>
        <h3>List Product</h3>
        <p>Add a new item to your shop.</p>
      </a>
    <?php endif; ?>
    <?php if ($user['role'] === 'admin'): ?>
      <a href="admin.php" class="quick-action-card quick-action-admin">
        <span class="quick-action-icon">⚙</span>
        <h3>Admin Console</h3>
        <p>Users, products and platform settings.</p>
      </a>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

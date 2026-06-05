<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

$stmt = $pdo->prepare("SELECT o.order_id, o.product_id, o.quantity, o.total_amount, o.payment_status, o.delivery_status, o.payment_reference, o.ordered_at, o.paid_at,
        p.title AS product_title, p.image_url, u.full_name AS seller_name
    FROM orders o
    LEFT JOIN products p ON o.product_id = p.product_id
    LEFT JOIN users u ON p.seller_id = u.user_id
    WHERE o.buyer_id = :buyer_id
    ORDER BY o.ordered_at DESC");
$stmt->execute([':buyer_id' => $_SESSION['user_id']]);
$orders = $stmt->fetchAll();

$pageTitle = 'My Orders — Ubuntu Market';
$bodyClass = 'orders-page';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-container">
  <div class="page-top">
    <div>
      <h1 class="section-title page-heading">My Orders</h1>
      <p class="page-subtitle">Track your purchases and payment details.</p>
    </div>
    <div class="page-actions">
      <a href="users.php" class="secondary-btn">Account</a>
      <a href="discovery.php" class="primary-btn">Continue Shopping</a>
    </div>
  </div>

  <?php if (empty($orders)): ?>
    <div class="empty-state">
      <h3>No orders yet</h3>
      <p>You haven't purchased anything yet. Browse Discover to find great local deals.</p>
      <a href="discovery.php" class="primary-btn">Browse Products</a>
    </div>
  <?php else: ?>
    <div class="order-list">
      <?php foreach ($orders as $order): ?>
        <article class="order-card">
          <div class="order-header">
            <div>
              <h2>Order #<?= (int) $order['order_id'] ?></h2>
              <p class="order-meta">Placed on <?= date('F j, Y', strtotime($order['ordered_at'])) ?> · Ref: <?= htmlspecialchars($order['payment_reference'] ?? '—') ?></p>
            </div>
            <span class="status-pill <?= $order['payment_status'] === 'paid' ? 'status-paid' : 'status-pending' ?>">
              <?= htmlspecialchars(ucfirst($order['payment_status'])) ?>
            </span>
          </div>
          <div class="order-body">
            <div class="order-product">
              <?php if (!empty($order['image_url'])): ?>
                <img src="<?= htmlspecialchars(asset_url($order['image_url'])) ?>" alt="<?= htmlspecialchars($order['product_title'] ?? '') ?>" loading="lazy">
              <?php endif; ?>
              <div>
                <h3><?= htmlspecialchars($order['product_title'] ?? 'Product unavailable') ?></h3>
                <p>Seller: <?= htmlspecialchars($order['seller_name'] ?? 'Unknown') ?></p>
                <p>Quantity: <?= (int) $order['quantity'] ?></p>
              </div>
            </div>
            <div class="order-total">
              <p>Total</p>
              <strong>R <?= number_format((float) $order['total_amount'], 2) ?></strong>
            </div>
          </div>
          <div class="order-footer">
            <p>Delivery: <?= htmlspecialchars(ucfirst($order['delivery_status'] ?? 'pending')) ?></p>
            <?php if (!empty($order['paid_at'])): ?>
              <p>Paid: <?= date('F j, Y g:i A', strtotime($order['paid_at'])) ?></p>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/reviews.php';

$portalType = 'seller';
$portalPage = 'dashboard';
$portalTitle = 'Seller Hub';
$portalHeading = 'Dashboard';
$portalSubtitle = 'Overview of your shop performance and recent activity.';
$pageTitle = 'Seller Dashboard';

$sellerId = (int) $_SESSION['user_id'];

$statsStmt = $pdo->prepare("
    SELECT
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_count,
        COUNT(*) AS total_listings,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_count
    FROM products
    WHERE seller_id = ?
");
$statsStmt->execute([$sellerId]);
$productStats = $statsStmt->fetch() ?: [];
$activeCount = (int) ($productStats['active_count'] ?? 0);
$totalListings = (int) ($productStats['total_listings'] ?? 0);
$pendingCount = (int) ($productStats['pending_count'] ?? 0);

$orderStatsStmt = $pdo->prepare("
    SELECT
        COUNT(o.order_id) AS order_count,
        COALESCE(SUM(CASE WHEN o.payment_status = 'paid' THEN o.total_amount ELSE 0 END), 0) AS revenue
    FROM orders o
    JOIN products p ON o.product_id = p.product_id
    WHERE p.seller_id = ?
");
$orderStatsStmt->execute([$sellerId]);
$orderStats = $orderStatsStmt->fetch() ?: [];
$orderCount = (int) ($orderStats['order_count'] ?? 0);
$revenue = (float) ($orderStats['revenue'] ?? 0);

$recentOrdersStmt = $pdo->prepare("SELECT o.order_id, o.quantity, o.total_amount, o.payment_status, o.delivery_status, o.ordered_at, p.title AS product_title
    FROM orders o JOIN products p ON o.product_id = p.product_id
    WHERE p.seller_id = ? ORDER BY o.ordered_at DESC LIMIT 5");
$recentOrdersStmt->execute([$sellerId]);
$recentOrders = $recentOrdersStmt->fetchAll();

$unreadNotifications = seller_unread_notification_count($pdo, $sellerId);
$recentNotifications = [];
try {
    $notificationsStmt = $pdo->prepare(
        'SELECT notification_id, order_id, message, created_at FROM seller_notifications
         WHERE seller_id = ? ORDER BY created_at DESC LIMIT 5'
    );
    $notificationsStmt->execute([$sellerId]);
    $recentNotifications = $notificationsStmt->fetchAll();
} catch (PDOException $e) {
    $recentNotifications = [];
}

$portalActions = '<a href="add-item.php" class="portal-btn portal-btn-primary">+ List New Item</a>';

include __DIR__ . '/../includes/portal-header.php';
?>

<div class="portal-stats">
  <div class="portal-stat-card portal-stat-card--listings">
    <div class="label">Active Listings</div>
    <div class="value"><?= $activeCount ?></div>
    <div class="hint"><?= $totalListings ?> total · <?= $pendingCount ?> pending</div>
  </div>
  <div class="portal-stat-card portal-stat-card--orders">
    <div class="label">Orders Received</div>
    <div class="value"><?= $orderCount ?></div>
    <div class="hint"><a href="seller-orders.php">View all orders →</a></div>
  </div>
  <div class="portal-stat-card portal-stat-card--revenue">
    <div class="label">Paid Revenue</div>
    <div class="value">R <?= number_format($revenue, 0) ?></div>
    <div class="hint">From completed payments</div>
  </div>
</div>

<?php if ($unreadNotifications > 0 || !empty($recentNotifications)): ?>
<div class="portal-panel">
  <div class="portal-panel-header">
    <h2>Order notifications<?= $unreadNotifications > 0 ? ' <span class="portal-nav-badge">' . $unreadNotifications . ' new</span>' : '' ?></h2>
    <a href="seller-orders.php" class="portal-btn portal-btn-secondary portal-btn-sm">View orders</a>
  </div>
  <?php if (empty($recentNotifications)): ?>
    <div class="portal-empty"><p>No notifications yet.</p></div>
  <?php else: ?>
    <ul class="portal-notification-list">
      <?php foreach ($recentNotifications as $note): ?>
        <li class="portal-notification-item">
          <strong><?= htmlspecialchars($note['message']) ?></strong>
          <span class="portal-order-meta"><?= date('M j, Y g:i A', strtotime($note['created_at'])) ?></span>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>
<?php endif; ?>

<div class="portal-panel">
  <div class="portal-panel-header">
    <h2>Recent orders</h2>
    <a href="seller-orders.php" class="portal-btn portal-btn-secondary portal-btn-sm">View all</a>
  </div>
  <?php if (empty($recentOrders)): ?>
    <div class="portal-empty">
      <h3>No orders yet</h3>
      <p>When buyers purchase your products, orders will appear here.</p>
      <a href="add-item.php" class="portal-btn portal-btn-primary">List your first product</a>
    </div>
  <?php else: ?>
    <div class="portal-order-list">
      <?php foreach ($recentOrders as $order): ?>
        <article class="portal-order-card">
          <div class="portal-order-card-body">
            <h3><?= htmlspecialchars($order['product_title']) ?></h3>
            <p class="portal-order-meta">Order #<?= (int) $order['order_id'] ?> · Qty <?= (int) $order['quantity'] ?> · R <?= number_format((float) $order['total_amount'], 2) ?></p>
            <p class="portal-order-meta">Delivery: <?= htmlspecialchars(ucfirst($order['delivery_status'] ?? 'pending')) ?></p>
          </div>
          <div class="portal-order-aside">
            <span class="portal-badge portal-badge-<?= htmlspecialchars($order['payment_status']) ?>"><?= htmlspecialchars($order['payment_status']) ?></span>
            <span class="portal-order-date"><?= date('M j, Y', strtotime($order['ordered_at'])) ?></span>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/portal-footer.php'; ?>

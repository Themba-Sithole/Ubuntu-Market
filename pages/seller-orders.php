<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/reviews.php';

$portalType = 'seller';
$portalPage = 'orders';
$portalTitle = 'Seller Hub';
$portalHeading = 'Orders';
$portalSubtitle = 'Track payments and update delivery status for your sales.';
$pageTitle = 'Seller Orders';

$sellerId = (int) $_SESSION['user_id'];
$message = '';
$messageType = 'success';
$deliveryOptions = ['pending', 'processing', 'shipped', 'delivered'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_delivery') {
    $orderId = (int) ($_POST['order_id'] ?? 0);
    $deliveryStatus = $_POST['delivery_status'] ?? '';
    if ($orderId > 0 && in_array($deliveryStatus, $deliveryOptions, true)) {
        $stmt = $pdo->prepare(
            "UPDATE orders o
             JOIN products p ON o.product_id = p.product_id
             SET o.delivery_status = :status
             WHERE o.order_id = :order_id AND p.seller_id = :seller_id AND o.payment_status = 'paid'"
        );
        $stmt->execute([
            ':status' => $deliveryStatus,
            ':order_id' => $orderId,
            ':seller_id' => $sellerId,
        ]);
        if ($stmt->rowCount() > 0) {
            $message = 'Delivery status updated for order #' . $orderId . '.';
        } else {
            $message = 'Could not update that order.';
            $messageType = 'error';
        }
    } else {
        $message = 'Invalid delivery status.';
        $messageType = 'error';
    }
}

mark_seller_notifications_read($pdo, $sellerId);

$orderStmt = $pdo->prepare(
    "SELECT o.order_id, o.product_id, o.quantity, o.total_amount, o.payment_status, o.delivery_status,
            o.payment_reference, o.ordered_at, p.title AS product_title, p.image_url
     FROM orders o
     JOIN products p ON o.product_id = p.product_id
     WHERE p.seller_id = :seller_id
     ORDER BY o.ordered_at DESC"
);
$orderStmt->execute([':seller_id' => $sellerId]);
$sellerOrders = $orderStmt->fetchAll();

include __DIR__ . '/../includes/portal-header.php';
?>

<?php if ($message): ?>
  <div class="portal-alert portal-alert-<?= $messageType === 'error' ? 'error' : 'success' ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="portal-panel">
  <div class="portal-panel-header">
    <h2><?= count($sellerOrders) ?> order<?= count($sellerOrders) === 1 ? '' : 's' ?></h2>
  </div>
  <?php if (empty($sellerOrders)): ?>
    <div class="portal-empty">
      <h3>No orders yet</h3>
      <p>Buyers will appear here once they place an order for your products.</p>
    </div>
  <?php else: ?>
    <div class="portal-order-list">
      <?php foreach ($sellerOrders as $order): ?>
        <article class="portal-order-card portal-order-card--actions">
          <div class="portal-order-card-body">
            <h3><?= htmlspecialchars($order['product_title']) ?></h3>
            <p class="portal-order-meta">Order #<?= (int) $order['order_id'] ?> · Qty <?= (int) $order['quantity'] ?> · R <?= number_format((float) $order['total_amount'], 2) ?></p>
            <p class="portal-order-meta">Ref: <?= htmlspecialchars($order['payment_reference'] ?? '—') ?></p>
          </div>
          <div class="portal-order-aside">
            <span class="portal-badge portal-badge-<?= htmlspecialchars($order['payment_status']) ?>"><?= htmlspecialchars($order['payment_status']) ?></span>
            <span class="portal-order-date"><?= date('M j, Y g:i A', strtotime($order['ordered_at'])) ?></span>
            <?php if ($order['payment_status'] === 'paid'): ?>
              <form method="post" class="portal-inline-form portal-delivery-form">
                <input type="hidden" name="action" value="update_delivery">
                <input type="hidden" name="order_id" value="<?= (int) $order['order_id'] ?>">
                <label class="portal-sr-only" for="delivery-<?= (int) $order['order_id'] ?>">Delivery status</label>
                <select name="delivery_status" id="delivery-<?= (int) $order['order_id'] ?>">
                  <?php foreach ($deliveryOptions as $opt): ?>
                    <option value="<?= $opt ?>" <?= ($order['delivery_status'] ?? 'pending') === $opt ? 'selected' : '' ?>><?= ucfirst($opt) ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="portal-btn portal-btn-secondary portal-btn-sm">Update</button>
              </form>
            <?php else: ?>
              <p class="portal-order-meta">Delivery: <?= htmlspecialchars(ucfirst($order['delivery_status'] ?? 'pending')) ?></p>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/portal-footer.php'; ?>

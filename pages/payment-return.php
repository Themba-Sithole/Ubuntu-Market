<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
require_once '../includes/payments.php';

requireLogin();

$paymentReference = $_GET['m_payment_id'] ?? $_SESSION['last_payment_reference'] ?? null;
$paymentProcessed = false;

if ($paymentReference) {
    $paymentProcessed = mark_orders_paid($pdo, (string) $paymentReference, (int) $_SESSION['user_id']);
    if ($paymentProcessed) {
        unset($_SESSION['cart'], $_SESSION['last_payment_reference']);
    }
}

$pageTitle = 'Payment Complete — Ubuntu Market';
$bodyClass = 'payment-page';
include __DIR__ . '/../includes/header.php';
?>

  <div class="page-container">
    <div class="page-top">
      <div>
        <h1 class="section-title page-heading">Payment completed</h1>
        <p class="page-subtitle">Thank you. Your payment was received successfully.</p>
      </div>
    </div>
    <div class="form-success">
      <?php if ($paymentProcessed): ?>
        <p>Your order is confirmed and your cart has been cleared.</p>
        <a href="<?= htmlspecialchars(site_url('pages/orders.php')) ?>" class="primary-btn">View my orders</a>
      <?php else: ?>
        <p>Payment was received, but we could not confirm the order automatically. Please check your orders page or contact support.</p>
        <a href="<?= htmlspecialchars(site_url('pages/orders.php')) ?>" class="secondary-btn">View my orders</a>
      <?php endif; ?>
    </div>
  </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

$pageTitle = 'Payment Cancelled — Ubuntu Market';
$bodyClass = 'payment-page';
include __DIR__ . '/../includes/header.php';
?>

  <div class="page-container">
    <div class="page-top">
      <div>
        <h1 class="section-title page-heading">Payment cancelled</h1>
        <p class="page-subtitle">Your payment was cancelled. You can return to the cart or continue shopping.</p>
      </div>
    </div>
    <div class="form-error">
      <p>The transaction was cancelled. No charges were made.</p>
      <a href="<?= htmlspecialchars(site_url('pages/cart.php')) ?>" class="secondary-btn">Back to cart</a>
      <a href="<?= htmlspecialchars(site_url('pages/discovery.php')) ?>" class="primary-btn">Continue shopping</a>
    </div>
  </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

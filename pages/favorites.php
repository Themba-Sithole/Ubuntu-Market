<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/reviews.php';

$favoriteIds = $_SESSION['favorites'] ?? [];
if (!is_array($favoriteIds)) {
    $favoriteIds = [];
}

if (isset($_GET['action'], $_GET['product_id'])) {
    $productId = (int) $_GET['product_id'];
    if ($_GET['action'] === 'remove') {
        $favoriteIds = array_filter($favoriteIds, function ($id) use ($productId) {
            return $id !== $productId;
        });
        $_SESSION['favorites'] = array_values($favoriteIds);
        header('Location: favorites.php');
        exit;
    }
}

$products = [];
if (count($favoriteIds) > 0) {
    $placeholders = implode(',', array_fill(0, count($favoriteIds), '?'));
    $stmt = $pdo->prepare("SELECT p.*, c.name AS category_name, b.name AS brand_name, u.full_name AS seller_name
        FROM products p
        JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN brands b ON p.brand_id = b.brand_id
        JOIN users u ON p.seller_id = u.user_id
        WHERE p.product_id IN ($placeholders) AND p.status = 'active'");
    $stmt->execute($favoriteIds);
    $products = $stmt->fetchAll();
}

$pageTitle = 'Wishlist — Ubuntu Market';
$bodyClass = 'favorites-page';
include __DIR__ . '/../includes/header.php';
?>

  <div class="page-container">
    <div class="page-top">
      <div>
        <h1 class="section-title page-heading">Wishlist</h1>
        <p class="page-subtitle">Your saved products in one place.</p>
      </div>
    </div>

    <?php if (count($products) === 0): ?>
      <div class="empty-state">
        <h3>No favorite items yet</h3>
        <p>Browse Discover or Shops and save items to view them here.</p>
      </div>
    <?php else: ?>
      <div class="products-grid">
        <?php foreach ($products as $product): ?>
          <?php $pid = (int) $product['product_id']; ?>
          <div class="product-card">
            <a href="product-view.php?product_id=<?= $pid ?>" class="product-card-link">
              <div class="product-img-wrap">
                <img src="<?= htmlspecialchars(asset_url($product['image_url'] ?? '')) ?>" alt="<?= htmlspecialchars($product['title']) ?>" loading="lazy" onerror="this.onerror=null;this.src='<?= htmlspecialchars(asset_url(''), ENT_QUOTES) ?>'">
              </div>
              <p class="product-name"><?= htmlspecialchars($product['title']) ?></p>
              <p class="product-rating"><?= htmlspecialchars(product_rating_label($pdo, $pid)) ?></p>
              <p class="product-price">R <?= number_format($product['price'], 2) ?></p>
              <p class="product-shop"><?= htmlspecialchars($product['seller_name']) ?></p>
            </a>
            <a href="favorites.php?action=remove&product_id=<?= $pid ?>" class="wishlist-btn wishlist-corner active" aria-label="Remove from wishlist">♥</a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

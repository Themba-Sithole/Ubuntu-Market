<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

$portalType = 'seller';
$portalPage = 'products';
$portalTitle = 'Seller Hub';
$portalHeading = 'My Products';
$portalSubtitle = 'Manage your listings, stock levels, and approval status.';
$pageTitle = 'My Products';

$sellerId = (int) $_SESSION['user_id'];

$productStmt = $pdo->prepare("SELECT p.product_id, p.title, p.price, p.quantity, p.status, p.image_url, p.created_at,
        c.name AS category_name, b.name AS brand_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    LEFT JOIN brands b ON p.brand_id = b.brand_id
    WHERE p.seller_id = :seller_id
    ORDER BY p.created_at DESC");
$productStmt->execute([':seller_id' => $sellerId]);
$products = $productStmt->fetchAll();

$portalActions = '<a href="add-item.php" class="portal-btn portal-btn-primary">+ List New Item</a>';

include __DIR__ . '/../includes/portal-header.php';
?>

<?php if (empty($products)): ?>
  <div class="portal-empty">
    <h3>No listings yet</h3>
    <p>Add your first product and start selling to buyers across South Africa.</p>
    <a href="add-item.php" class="portal-btn portal-btn-primary">Start Selling</a>
  </div>
<?php else: ?>
  <div class="portal-panel">
    <div class="portal-panel-header">
      <h2><?= count($products) ?> product<?= count($products) === 1 ? '' : 's' ?></h2>
    </div>
    <div class="portal-table-wrap">
      <table class="portal-table">
        <thead>
          <tr><th></th><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Listed</th></tr>
        </thead>
        <tbody>
          <?php foreach ($products as $product): ?>
            <tr>
              <td>
                <?php if (!empty($product['image_url'])): ?>
                  <img src="<?= htmlspecialchars(asset_url($product['image_url'])) ?>" alt="" class="portal-product-thumb" loading="lazy">
                <?php endif; ?>
              </td>
              <td>
                <strong><?= htmlspecialchars($product['title']) ?></strong>
                <?php if ($product['brand_name']): ?>
                  <br><span style="font-size:12px;color:#64748b"><?= htmlspecialchars($product['brand_name']) ?></span>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($product['category_name'] ?? '—') ?></td>
              <td>R <?= number_format((float) $product['price'], 2) ?></td>
              <td><?= (int) $product['quantity'] ?></td>
              <td><span class="portal-badge portal-badge-<?= htmlspecialchars($product['status']) ?>"><?= htmlspecialchars($product['status']) ?></span></td>
              <td>
                <?= date('M j, Y', strtotime($product['created_at'])) ?>
                <br><a href="edit-item.php?product_id=<?= (int) $product['product_id'] ?>" class="portal-btn portal-btn-secondary portal-btn-sm" style="margin-top:6px">Edit</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/portal-footer.php'; ?>

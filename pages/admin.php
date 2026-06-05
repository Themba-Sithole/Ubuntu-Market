<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

$portalType = 'admin';
$tab = $_GET['tab'] ?? 'dashboard';
$allowedTabs = ['dashboard', 'users', 'products', 'orders', 'reviews'];
if (!in_array($tab, $allowedTabs, true)) {
    $tab = 'dashboard';
}

$portalPage = $tab === 'dashboard' ? 'dashboard' : $tab;
$portalTitle = 'Admin Console';
$portalHeading = match ($tab) {
    'users' => 'User Management',
    'products' => 'Product Management',
    'orders' => 'All Orders',
    'reviews' => 'Review Moderation',
    default => 'Platform Dashboard',
};
$portalSubtitle = 'Manage users, listings, and marketplace activity (RBAC).';
$pageTitle = $portalHeading;

$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_role') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $newRole = $_POST['role'] ?? '';
        if ($userId > 0 && in_array($newRole, ['buyer', 'seller', 'admin'], true) && $userId !== (int) $_SESSION['user_id']) {
            $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE user_id = ?');
            $stmt->execute([$newRole, $userId]);
            $message = 'User role updated successfully.';
        } else {
            $message = 'Unable to update that user role.';
            $messageType = 'error';
        }
    }

    if ($action === 'update_product_status') {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        if ($productId > 0 && in_array($status, ['active', 'pending', 'inactive'], true)) {
            $stmt = $pdo->prepare('UPDATE products SET status = ? WHERE product_id = ?');
            $stmt->execute([$status, $productId]);
            $message = 'Product status updated.';
        } else {
            $message = 'Invalid product status.';
            $messageType = 'error';
        }
    }

    if ($action === 'update_review_status') {
        $reviewId = (int) ($_POST['review_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        if ($reviewId > 0 && in_array($status, ['pending', 'approved', 'hidden'], true)) {
            $stmt = $pdo->prepare('UPDATE reviews SET status = ? WHERE review_id = ?');
            $stmt->execute([$status, $reviewId]);
            $message = 'Review status updated.';
        } else {
            $message = 'Invalid review status.';
            $messageType = 'error';
        }
    }

    if ($action === 'delete_review') {
        $reviewId = (int) ($_POST['review_id'] ?? 0);
        if ($reviewId > 0) {
            $pdo->prepare('DELETE FROM reviews WHERE review_id = ?')->execute([$reviewId]);
            $message = 'Review removed.';
        }
    }

    if ($action === 'update_order_delivery') {
        $orderId = (int) ($_POST['order_id'] ?? 0);
        $status = $_POST['delivery_status'] ?? '';
        if ($orderId > 0 && in_array($status, ['pending', 'processing', 'shipped', 'delivered'], true)) {
            $stmt = $pdo->prepare('UPDATE orders SET delivery_status = ? WHERE order_id = ?');
            $stmt->execute([$status, $orderId]);
            $message = 'Order delivery status updated.';
        }
    }
}

$userCount = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$productCount = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$orderCount = (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$pendingProducts = (int) $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'pending'")->fetchColumn();
$reviewCount = 0;
$pendingReviews = 0;
try {
    $reviewCount = (int) $pdo->query('SELECT COUNT(*) FROM reviews')->fetchColumn();
    $pendingReviews = (int) $pdo->query("SELECT COUNT(*) FROM reviews WHERE status = 'pending'")->fetchColumn();
} catch (PDOException $e) {
}

$users = $pdo->query('SELECT user_id, full_name, email, role, created_at FROM users ORDER BY created_at DESC')->fetchAll();
$products = $pdo->query("SELECT p.product_id, p.title, p.price, p.status, p.created_at, p.image_url, u.full_name AS seller_name
    FROM products p JOIN users u ON p.seller_id = u.user_id ORDER BY p.created_at DESC LIMIT 100")->fetchAll();
$orders = $pdo->query("SELECT o.order_id, o.total_amount, o.payment_status, o.delivery_status, o.ordered_at,
        p.title AS product_title, b.full_name AS buyer_name
    FROM orders o
    LEFT JOIN products p ON o.product_id = p.product_id
    LEFT JOIN users b ON o.buyer_id = b.user_id
    ORDER BY o.ordered_at DESC LIMIT 50")->fetchAll();

$allReviews = [];
try {
    $allReviews = $pdo->query(
        "SELECT r.review_id, r.rating, r.comment, r.status, r.created_at,
                p.title AS product_title, u.full_name AS buyer_name
         FROM reviews r
         JOIN products p ON r.product_id = p.product_id
         JOIN users u ON r.buyer_id = u.user_id
         ORDER BY r.created_at DESC LIMIT 100"
    )->fetchAll();
} catch (PDOException $e) {
    $allReviews = [];
}

include __DIR__ . '/../includes/portal-header.php';
?>

<?php if ($message): ?>
  <div class="portal-alert portal-alert-<?= $messageType === 'error' ? 'error' : 'success' ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<nav class="portal-tabs" aria-label="Admin sections">
  <a href="admin.php" class="portal-tab<?= $tab === 'dashboard' ? ' active' : '' ?>">Dashboard</a>
  <a href="admin.php?tab=users" class="portal-tab<?= $tab === 'users' ? ' active' : '' ?>">Users</a>
  <a href="admin.php?tab=products" class="portal-tab<?= $tab === 'products' ? ' active' : '' ?>">Products</a>
  <a href="admin.php?tab=orders" class="portal-tab<?= $tab === 'orders' ? ' active' : '' ?>">Orders</a>
  <a href="admin.php?tab=reviews" class="portal-tab<?= $tab === 'reviews' ? ' active' : '' ?>">Reviews</a>
</nav>

<?php if ($tab === 'dashboard'): ?>
  <div class="portal-stats">
    <div class="portal-stat-card">
      <div class="label">Total Users</div>
      <div class="value"><?= $userCount ?></div>
      <div class="hint">Buyers, sellers &amp; admins</div>
    </div>
    <div class="portal-stat-card">
      <div class="label">Listings</div>
      <div class="value"><?= $productCount ?></div>
      <div class="hint"><?= $pendingProducts ?> pending approval</div>
    </div>
    <div class="portal-stat-card">
      <div class="label">Orders</div>
      <div class="value"><?= $orderCount ?></div>
      <div class="hint">All marketplace orders</div>
    </div>
    <div class="portal-stat-card">
      <div class="label">Reviews</div>
      <div class="value"><?= $reviewCount ?></div>
      <div class="hint"><?= $pendingReviews ?> pending moderation</div>
    </div>
  </div>

  <div class="portal-panel">
    <div class="portal-panel-header">
      <h2>Pending product approvals</h2>
      <a href="admin.php?tab=products" class="portal-btn portal-btn-secondary portal-btn-sm">View all</a>
    </div>
    <?php
    $pending = array_filter($products, fn($p) => ($p['status'] ?? '') === 'pending');
    if (empty($pending)):
    ?>
      <div class="portal-empty"><p>No products awaiting approval.</p></div>
    <?php else: ?>
      <div class="portal-table-wrap">
        <table class="portal-table">
          <thead><tr><th>Product</th><th>Seller</th><th>Price</th><th>Action</th></tr></thead>
          <tbody>
            <?php foreach (array_slice($pending, 0, 5) as $product): ?>
              <tr>
                <td><?= htmlspecialchars($product['title']) ?></td>
                <td><?= htmlspecialchars($product['seller_name']) ?></td>
                <td>R <?= number_format((float) $product['price'], 2) ?></td>
                <td>
                  <form method="post" class="portal-inline-form">
                    <input type="hidden" name="action" value="update_product_status">
                    <input type="hidden" name="product_id" value="<?= (int) $product['product_id'] ?>">
                    <input type="hidden" name="status" value="active">
                    <button type="submit" class="portal-btn portal-btn-primary portal-btn-sm">Approve</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php if ($tab === 'users'): ?>
  <div class="portal-panel">
    <div class="portal-panel-header"><h2>User management (RBAC)</h2></div>
    <div class="portal-table-wrap">
      <table class="portal-table">
        <thead>
          <tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Change role</th></tr>
        </thead>
        <tbody>
          <?php foreach ($users as $user): ?>
            <tr>
              <td><strong><?= htmlspecialchars($user['full_name']) ?></strong></td>
              <td><?= htmlspecialchars($user['email']) ?></td>
              <td><span class="portal-badge portal-badge-<?= htmlspecialchars($user['role']) ?>"><?= htmlspecialchars($user['role']) ?></span></td>
              <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
              <td>
                <?php if ((int) $user['user_id'] === (int) $_SESSION['user_id']): ?>
                  <span class="portal-muted-text">Current account</span>
                <?php else: ?>
                  <form method="post" class="portal-inline-form">
                    <input type="hidden" name="action" value="update_role">
                    <input type="hidden" name="user_id" value="<?= (int) $user['user_id'] ?>">
                    <select name="role" aria-label="Role for <?= htmlspecialchars($user['full_name']) ?>">
                      <option value="buyer" <?= $user['role'] === 'buyer' ? 'selected' : '' ?>>Buyer</option>
                      <option value="seller" <?= $user['role'] === 'seller' ? 'selected' : '' ?>>Seller</option>
                      <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                    <button type="submit" class="portal-btn portal-btn-secondary portal-btn-sm">Save</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<?php if ($tab === 'products'): ?>
  <div class="portal-panel">
    <div class="portal-panel-header"><h2>All products</h2></div>
    <div class="portal-table-wrap">
      <table class="portal-table">
        <thead>
          <tr><th></th><th>Title</th><th>Seller</th><th>Price</th><th>Status</th><th>Update</th></tr>
        </thead>
        <tbody>
          <?php foreach ($products as $product): ?>
            <tr>
              <td>
                <?php if (!empty($product['image_url'])): ?>
                  <img src="<?= htmlspecialchars(asset_url($product['image_url'])) ?>" alt="" class="portal-product-thumb" loading="lazy">
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($product['title']) ?></td>
              <td><?= htmlspecialchars($product['seller_name']) ?></td>
              <td>R <?= number_format((float) $product['price'], 2) ?></td>
              <td><span class="portal-badge portal-badge-<?= htmlspecialchars($product['status']) ?>"><?= htmlspecialchars($product['status']) ?></span></td>
              <td>
                <form method="post" class="portal-inline-form">
                  <input type="hidden" name="action" value="update_product_status">
                  <input type="hidden" name="product_id" value="<?= (int) $product['product_id'] ?>">
                  <select name="status">
                    <option value="pending" <?= $product['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="active" <?= $product['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $product['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                  </select>
                  <button type="submit" class="portal-btn portal-btn-secondary portal-btn-sm">Update</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<?php if ($tab === 'orders'): ?>
  <div class="portal-panel">
    <div class="portal-panel-header"><h2>Marketplace orders</h2></div>
    <?php if (empty($orders)): ?>
      <div class="portal-empty"><p>No orders recorded yet.</p></div>
    <?php else: ?>
      <?php foreach ($orders as $order): ?>
        <div class="portal-order-card">
          <h3>Order #<?= (int) $order['order_id'] ?> — <?= htmlspecialchars($order['product_title'] ?? 'Product') ?></h3>
          <p class="portal-order-meta">Buyer: <?= htmlspecialchars($order['buyer_name'] ?? 'Unknown') ?> · R <?= number_format((float) $order['total_amount'], 2) ?></p>
          <p class="portal-order-meta">
            Payment: <span class="portal-badge portal-badge-<?= htmlspecialchars($order['payment_status']) ?>"><?= htmlspecialchars($order['payment_status']) ?></span>
            · <?= date('M j, Y g:i A', strtotime($order['ordered_at'])) ?>
          </p>
          <form method="post" class="portal-inline-form portal-delivery-form">
            <input type="hidden" name="action" value="update_order_delivery">
            <input type="hidden" name="order_id" value="<?= (int) $order['order_id'] ?>">
            <select name="delivery_status">
              <?php foreach (['pending', 'processing', 'shipped', 'delivered'] as $opt): ?>
                <option value="<?= $opt ?>" <?= ($order['delivery_status'] ?? 'pending') === $opt ? 'selected' : '' ?>><?= ucfirst($opt) ?></option>
              <?php endforeach; ?>
            </select>
            <button type="submit" class="portal-btn portal-btn-secondary portal-btn-sm">Update delivery</button>
          </form>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php if ($tab === 'reviews'): ?>
  <div class="portal-panel">
    <div class="portal-panel-header"><h2>Customer reviews</h2></div>
    <?php if (empty($allReviews)): ?>
      <div class="portal-empty"><p>No reviews submitted yet.</p></div>
    <?php else: ?>
      <div class="portal-table-wrap">
        <table class="portal-table">
          <thead>
            <tr><th>Product</th><th>Buyer</th><th>Rating</th><th>Comment</th><th>Status</th><th>Actions</th></tr>
          </thead>
          <tbody>
            <?php foreach ($allReviews as $review): ?>
              <tr>
                <td><?= htmlspecialchars($review['product_title']) ?></td>
                <td><?= htmlspecialchars($review['buyer_name']) ?></td>
                <td><?= (int) $review['rating'] ?>/5</td>
                <td><?= htmlspecialchars(mb_strimwidth($review['comment'], 0, 80, '…')) ?></td>
                <td><span class="portal-badge portal-badge-<?= htmlspecialchars($review['status']) ?>"><?= htmlspecialchars($review['status']) ?></span></td>
                <td>
                  <form method="post" class="portal-inline-form">
                    <input type="hidden" name="action" value="update_review_status">
                    <input type="hidden" name="review_id" value="<?= (int) $review['review_id'] ?>">
                    <select name="status">
                      <option value="pending" <?= $review['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                      <option value="approved" <?= $review['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                      <option value="hidden" <?= $review['status'] === 'hidden' ? 'selected' : '' ?>>Hidden</option>
                    </select>
                    <button type="submit" class="portal-btn portal-btn-secondary portal-btn-sm">Save</button>
                  </form>
                  <form method="post" class="portal-inline-form" onsubmit="return confirm('Delete this review?');">
                    <input type="hidden" name="action" value="delete_review">
                    <input type="hidden" name="review_id" value="<?= (int) $review['review_id'] ?>">
                    <button type="submit" class="portal-btn portal-btn-sm" style="color:#b91c1c">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/portal-footer.php'; ?>

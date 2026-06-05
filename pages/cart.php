<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cartCount = array_sum($_SESSION['cart']);
$message = $_SESSION['cart_message'] ?? '';
unset($_SESSION['cart_message']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

    if ($productId > 0) {
        $stockStmt = $pdo->prepare("SELECT quantity FROM products WHERE product_id = ? AND status = 'active'");
        $stockStmt->execute([$productId]);
        $stockRow = $stockStmt->fetch();
        $available = $stockRow ? max(0, (int) $stockRow['quantity']) : 0;

        switch ($action) {
            case 'add':
                $qty = max(1, (int)($_POST['quantity'] ?? 1));
                if ($available < 1) {
                    $_SESSION['cart_message'] = 'That item is out of stock.';
                    header('Location: cart.php');
                    exit;
                }
                $current = (int) ($_SESSION['cart'][$productId] ?? 0);
                $newQty = min($available, $current + $qty);
                $_SESSION['cart'][$productId] = $newQty;
                if ($newQty < $current + $qty) {
                    $_SESSION['cart_message'] = 'Some items could not be added — quantity limited by stock.';
                } else {
                    $_SESSION['cart_message'] = 'Item added to cart.';
                }
                header('Location: cart.php');
                exit;
            case 'remove':
                unset($_SESSION['cart'][$productId]);
                $_SESSION['cart_message'] = 'Item removed from cart.';
                header('Location: cart.php');
                exit;
            case 'update':
                $qty = max(1, (int)($_POST['quantity'] ?? 1));
                if ($available < 1) {
                    unset($_SESSION['cart'][$productId]);
                    $_SESSION['cart_message'] = 'Item removed — out of stock.';
                } else {
                    $_SESSION['cart'][$productId] = min($available, $qty);
                    if (min($available, $qty) < $qty) {
                        $_SESSION['cart_message'] = 'Quantity adjusted to match available stock.';
                    } else {
                        $_SESSION['cart_message'] = 'Cart updated.';
                    }
                }
                header('Location: cart.php');
                exit;
        }
    }
}

$cartProductIds = array_keys($_SESSION['cart']);
$cartItems = [];
$totalAmount = 0.0;

if (count($cartProductIds) > 0) {
    $placeholders = implode(',', array_fill(0, count($cartProductIds), '?'));
    $stmt = $pdo->prepare("SELECT p.product_id, p.title, p.price, p.quantity AS available_quantity, p.image_url, u.full_name AS seller_name
        FROM products p
        JOIN users u ON p.seller_id = u.user_id
        WHERE p.product_id IN ($placeholders) AND p.status = 'active'");
    $stmt->execute($cartProductIds);
    $cartItems = $stmt->fetchAll();

    $validIds = array_map('intval', array_column($cartItems, 'product_id'));
    foreach (array_keys($_SESSION['cart']) as $cid) {
        if (!in_array((int) $cid, $validIds, true)) {
            unset($_SESSION['cart'][$cid]);
        }
    }

    $totalAmount = 0.0;
    foreach ($cartItems as &$item) {
        $pid = (int) $item['product_id'];
        $available = max(0, (int) $item['available_quantity']);
        $cartQty = (int) ($_SESSION['cart'][$pid] ?? 0);
        if ($cartQty > $available) {
            $cartQty = $available;
            if ($cartQty < 1) {
                unset($_SESSION['cart'][$pid]);
                continue;
            }
            $_SESSION['cart'][$pid] = $cartQty;
        }
        $item['cart_quantity'] = $cartQty;
        $item['subtotal'] = $item['price'] * $item['cart_quantity'];
        $totalAmount += $item['subtotal'];
    }
    unset($item);

    $cartItems = array_values(array_filter($cartItems, function ($row) {
        return isset($_SESSION['cart'][$row['product_id']]);
    }));
}
?>
<?php
$pageTitle = 'Cart — Ubuntu Market';
$bodyClass = 'cart-page';
include __DIR__ . '/../includes/header.php';
?>
  <div class="page-container">
    <div class="page-top">
      <div>
        <h1 class="section-title page-heading">Your Cart</h1>
        <p class="page-subtitle">Review your items before secure checkout.</p>
      </div>
    </div>

    <?php if ($message): ?>
      <div class="form-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if (empty($cartItems)): ?>
      <div class="empty-state">
        <h3>Your cart is empty</h3>
        <p>Add items from Discover or Shops to see them here.</p>
        <a href="discovery.php" class="primary-btn">Browse products</a>
      </div>
    <?php else: ?>
      <div class="cart-layout">
        <div class="cart-list">
          <table class="cart-table">
            <thead>
              <tr>
                <th>Product</th>
                <th>Seller</th>
                <th>Price</th>
                <th>Qty</th>
                <th>Subtotal</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($cartItems as $item): ?>
                <tr>
                  <td class="cart-product-cell">
                    <img src="<?= htmlspecialchars(asset_url($item['image_url'] ?? '')) ?>" alt="<?= htmlspecialchars($item['title']) ?>" loading="lazy" onerror="this.onerror=null;this.src='<?= htmlspecialchars(asset_url(''), ENT_QUOTES) ?>'">
                    <div>
                      <a href="product-view.php?product_id=<?= (int) $item['product_id'] ?>" class="product-name-link"><?= htmlspecialchars($item['title']) ?></a>
                    </div>
                  </td>
                  <td><?= htmlspecialchars($item['seller_name']) ?></td>
                  <td>R <?= number_format($item['price'], 2) ?></td>
                  <td>
                    <form method="post" action="cart.php" class="cart-update-form">
                      <input type="hidden" name="action" value="update">
                      <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                      <input type="number" name="quantity" value="<?= $item['cart_quantity'] ?>" min="1">
                      <button type="submit" class="text-button">Update</button>
                    </form>
                  </td>
                  <td>R <?= number_format($item['subtotal'], 2) ?></td>
                  <td>
                    <form method="post" action="cart.php">
                      <input type="hidden" name="action" value="remove">
                      <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                      <button type="submit" class="text-button">Remove</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <aside class="cart-summary">
          <h2>Order summary</h2>
          <div class="summary-row">
            <span>Items</span>
            <span><?= count($cartItems) ?></span>
          </div>
          <div class="summary-row total-row">
            <strong>Total</strong>
            <strong>R <?= number_format($totalAmount, 2) ?></strong>
          </div>
          <form method="post" action="process-payment.php">
            <input type="hidden" name="action" value="checkout">
            <button type="submit" class="primary-btn">Proceed to Pay</button>
          </form>
        </aside>
      </div>
    <?php endif; ?>
  </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

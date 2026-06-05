<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

requireLogin();

$errors = [];
$successMessage = '';
$userRole = userRole();
$productId = isset($_GET['product_id']) ? (int) $_GET['product_id'] : (int) ($_POST['product_id'] ?? 0);

if ($productId <= 0) {
    header('Location: seller-products.php');
    exit;
}

if (!in_array($userRole, ['seller', 'admin'], true)) {
    header('Location: users.php');
    exit;
}

$categories = get_categories($pdo);
$brands = get_brands($pdo);

$loadStmt = $pdo->prepare('SELECT * FROM products WHERE product_id = ?');
$loadStmt->execute([$productId]);
$product = $loadStmt->fetch();

if (!$product) {
    header('Location: seller-products.php');
    exit;
}

if ($userRole === 'seller' && (int) $product['seller_id'] !== (int) $_SESSION['user_id']) {
    header('Location: seller-products.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $categoryId = $_POST['category_id'] ?? '';
    $brandId = $_POST['brand_id'] ?? '';
    $price = $_POST['price'] ?? '';
    $quantity = $_POST['quantity'] ?? '';

    if ($title === '') {
        $errors[] = 'Please enter a product title.';
    }
    if ($description === '') {
        $errors[] = 'Please add a description.';
    }
    if ($categoryId === '') {
        $errors[] = 'Please choose a category.';
    }
    if ($price === '' || !is_numeric($price) || $price < 0) {
        $errors[] = 'Please enter a valid price.';
    }
    if ($quantity === '' || !filter_var($quantity, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]])) {
        $errors[] = 'Please enter a valid quantity.';
    }

    $imageUrl = $product['image_url'];
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['image']['type'], $allowedTypes, true)) {
            $errors[] = 'Please upload a JPG, PNG or GIF image.';
        } else {
            $uploadDir = __DIR__ . '/../images/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('prod_', true) . '.' . $extension;
            $destination = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                $imageUrl = 'images/products/' . $filename;
            } else {
                $errors[] = 'Unable to save the photo. Please try again.';
            }
        }
    }

    if (empty($errors)) {
        $newStatus = $product['status'];
        if ($userRole === 'seller' && $product['status'] === 'active') {
            $changed = $title !== $product['title']
                || $description !== $product['description']
                || (string) $categoryId !== (string) $product['category_id']
                || (string) ($brandId ?: '') !== (string) ($product['brand_id'] ?? '')
                || (float) $price !== (float) $product['price']
                || $imageUrl !== $product['image_url'];
            if ($changed) {
                $newStatus = 'pending';
            }
        }

        $stmt = $pdo->prepare(
            'UPDATE products SET category_id = :category_id, brand_id = :brand_id, title = :title,
             description = :description, price = :price, quantity = :quantity, image_url = :image_url,
             status = :status WHERE product_id = :product_id'
        );
        $stmt->execute([
            ':category_id' => $categoryId,
            ':brand_id' => $brandId ?: null,
            ':title' => $title,
            ':description' => $description,
            ':price' => $price,
            ':quantity' => $quantity,
            ':image_url' => $imageUrl,
            ':status' => $newStatus,
            ':product_id' => $productId,
        ]);

        $successMessage = $newStatus === 'pending'
            ? 'Product updated and sent for re-approval.'
            : 'Product updated successfully.';
        $loadStmt->execute([$productId]);
        $product = $loadStmt->fetch();
    }
}

$portalType = 'seller';
$portalPage = 'products';
$portalTitle = 'Seller Hub';
$portalHeading = 'Edit Product';
$portalSubtitle = 'Update listing details, price, stock, or photo.';
$pageTitle = 'Edit Product';

include __DIR__ . '/../includes/portal-header.php';
?>

<div class="portal-panel portal-form-panel">
  <div class="portal-panel-header">
    <h2><?= htmlspecialchars($product['title']) ?></h2>
    <a href="seller-products.php" class="portal-btn portal-btn-secondary portal-btn-sm">← Back to products</a>
  </div>
  <div class="portal-form-body">
    <?php if (!empty($errors)): ?>
      <div class="portal-alert portal-alert-error">
        <ul>
          <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if ($successMessage): ?>
      <div class="portal-alert portal-alert-success"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>

    <form action="edit-item.php?product_id=<?= $productId ?>" method="POST" enctype="multipart/form-data" class="add-item-form">
      <input type="hidden" name="product_id" value="<?= $productId ?>">

      <div class="form-row">
        <label for="image">Replace photo (optional)</label>
        <?php if (!empty($product['image_url'])): ?>
          <img src="<?= htmlspecialchars(asset_url($product['image_url'])) ?>" alt="" class="portal-product-thumb" style="margin-bottom:8px">
        <?php endif; ?>
        <input type="file" id="image" name="image" accept="image/*">
      </div>

      <div class="form-row">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" value="<?= htmlspecialchars($product['title']) ?>" required>
      </div>

      <div class="form-row">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="5" required><?= htmlspecialchars($product['description']) ?></textarea>
      </div>

      <div class="form-row two-columns">
        <div>
          <label for="category_id">Category</label>
          <select id="category_id" name="category_id" required>
            <?php foreach ($categories as $category): ?>
              <option value="<?= $category['category_id'] ?>" <?= (int) $product['category_id'] === (int) $category['category_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($category['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label for="brand_id">Brand</label>
          <select id="brand_id" name="brand_id">
            <option value="">Choose brand</option>
            <?php foreach ($brands as $brand): ?>
              <option value="<?= $brand['brand_id'] ?>" <?= (int) ($product['brand_id'] ?? 0) === (int) $brand['brand_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($brand['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-row two-columns">
        <div>
          <label for="price">Price</label>
          <input type="number" id="price" name="price" step="0.01" min="0" value="<?= htmlspecialchars((string) $product['price']) ?>" required>
        </div>
        <div>
          <label for="quantity">Quantity</label>
          <input type="number" id="quantity" name="quantity" min="0" value="<?= (int) $product['quantity'] ?>" required>
        </div>
      </div>

      <p class="portal-muted-text">Status: <span class="portal-badge portal-badge-<?= htmlspecialchars($product['status']) ?>"><?= htmlspecialchars($product['status']) ?></span></p>

      <button type="submit" class="portal-btn portal-btn-primary">Save changes</button>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../includes/portal-footer.php'; ?>

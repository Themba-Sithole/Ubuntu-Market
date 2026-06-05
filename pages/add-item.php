<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

$errors = [];
$successMessage = '';
$userRole = userRole();

require_once __DIR__ . '/../includes/helpers.php';
$categories = get_categories($pdo);
$brands = get_brands($pdo);

if (!in_array($userRole, ['seller', 'admin'], true)) {
    $errors[] = 'Only sellers can list new products. Please register as a seller or update your account to sell items.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)) {
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
    if ($quantity === '' || !filter_var($quantity, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
        $errors[] = 'Please enter a valid quantity.';
    }

    $imageUrl = 'images/placeholder.png';
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
        $stmt = $pdo->prepare("INSERT INTO products (seller_id, category_id, brand_id, title, description, price, quantity, image_url, status, created_at)
            VALUES (:seller_id, :category_id, :brand_id, :title, :description, :price, :quantity, :image_url, 'pending', NOW())");
        $stmt->execute([
            ':seller_id' => $_SESSION['user_id'],
            ':category_id' => $categoryId,
            ':brand_id' => $brandId ?: null,
            ':title' => $title,
            ':description' => $description,
            ':price' => $price,
            ':quantity' => $quantity,
            ':image_url' => $imageUrl,
        ]);

        $successMessage = 'Your product has been submitted for review and is now pending approval.';
    }
}

$pageTitle = 'List an Item — Ubuntu Market';
$bodyClass = 'add-item-page';
$usePortal = in_array($userRole, ['seller', 'admin'], true);

if ($usePortal) {
    $portalType = 'seller';
    $portalPage = 'add-item';
    $portalTitle = 'Seller Hub';
    $portalHeading = 'List New Item';
    $portalSubtitle = 'Add product details and upload a photo. Listings are reviewed before going live.';
    include __DIR__ . '/../includes/portal-header.php';
    echo '<div class="portal-panel portal-form-panel"><div class="portal-panel-header"><h2>Product details</h2></div><div class="portal-form-body">';
} else {
    include __DIR__ . '/../includes/header.php';
    echo '<div class="page-container"><div class="page-top"><div><h1 class="section-title page-heading">List a new item</h1><p class="page-subtitle">Become a seller to publish listings on Ubuntu Market.</p></div></div><div class="form-card">';
}
?>

<?php /* form body continues */ ?>
      <?php if (!empty($errors)): ?>
        <div class="form-error">
          <ul>
            <?php foreach ($errors as $error): ?>
              <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <?php if ($successMessage): ?>
        <div class="form-success"><?= htmlspecialchars($successMessage) ?></div>
      <?php endif; ?>

      <?php if ($userRole === 'seller' || $userRole === 'admin'): ?>
        <form action="add-item.php" method="POST" enctype="multipart/form-data" class="add-item-form">
          <div class="form-row">
            <label for="image">Upload photo</label>
            <input type="file" id="image" name="image" accept="image/*">
          </div>

        <div class="form-row">
          <label for="title">Title</label>
          <input type="text" id="title" name="title" placeholder="Enter your item title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
        </div>

        <div class="form-row">
          <label for="description">Description</label>
          <textarea id="description" name="description" placeholder="Describe the item" rows="5" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </div>

        <div class="form-row two-columns">
          <div>
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id" required>
              <option value="">Choose category</option>
              <?php foreach ($categories as $category): ?>
                <option value="<?= $category['category_id'] ?>" <?= (isset($_POST['category_id']) && $_POST['category_id'] == $category['category_id']) ? 'selected' : '' ?>>
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
                <option value="<?= $brand['brand_id'] ?>" <?= (isset($_POST['brand_id']) && $_POST['brand_id'] == $brand['brand_id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($brand['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-row two-columns">
          <div>
            <label for="price">Price</label>
            <input type="number" id="price" name="price" placeholder="R 0" step="0.01" min="0" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>" required>
          </div>

          <div>
            <label for="quantity">Quantity</label>
            <input type="number" id="quantity" name="quantity" placeholder="1" min="1" value="<?= htmlspecialchars($_POST['quantity'] ?? '1') ?>" required>
          </div>
        </div>

        <button type="submit" class="portal-btn portal-btn-primary">Submit for review</button>
      </form>
      <?php else: ?>
        <div class="portal-alert portal-alert-error">
          <p>Only sellers can list products. Register with a seller account or contact support to upgrade your role.</p>
          <p style="margin-top:12px"><a href="users.php" class="portal-btn portal-btn-secondary">My Account</a></p>
        </div>
      <?php endif; ?>
  </div>
</div>

<?php
if ($usePortal) {
    include __DIR__ . '/../includes/portal-footer.php';
} else {
    include __DIR__ . '/../includes/footer.php';
}
?>

<?php
// main product browsing page with search and filters
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

$favoriteIds = $_SESSION['favorites'] ?? [];
if (!is_array($favoriteIds)) {
    $favoriteIds = [];
}

$search = trim($_GET['search'] ?? '');
$categoryId = $_GET['category_id'] ?? '';
$brandId = $_GET['brand_id'] ?? '';
$priceMin = $_GET['price_min'] ?? '';
$priceMax = $_GET['price_max'] ?? '';
$currentPage = paginate_page();
$perPage = products_per_page();
$offset = ($currentPage - 1) * $perPage;

$categories = get_categories($pdo);
$brands = get_brands($pdo);

$where = ["p.status = 'active'"];
$params = [];

if ($search !== '') {
    $where[] = '(p.title LIKE :search OR p.description LIKE :search OR c.name LIKE :search OR b.name LIKE :search)';
    $params[':search'] = '%' . $search . '%';
}
if ($categoryId !== '') {
    $where[] = 'p.category_id = :category_id';
    $params[':category_id'] = $categoryId;
}
if ($brandId !== '') {
    $where[] = 'p.brand_id = :brand_id';
    $params[':brand_id'] = $brandId;
}
if ($priceMin !== '' && is_numeric($priceMin)) {
    $where[] = 'p.price >= :price_min';
    $params[':price_min'] = $priceMin;
}
if ($priceMax !== '' && is_numeric($priceMax)) {
    $where[] = 'p.price <= :price_max';
    $params[':price_max'] = $priceMax;
}

$fromSql = 'FROM products p
        JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN brands b ON p.brand_id = b.brand_id
        JOIN users u ON p.seller_id = u.user_id
        LEFT JOIN seller_verification sv ON sv.user_id = p.seller_id
        WHERE ' . implode(' AND ', $where);

$countStmt = $pdo->prepare('SELECT COUNT(*) ' . $fromSql);
$countStmt->execute($params);
$totalProducts = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalProducts / $perPage));
if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
    $offset = ($currentPage - 1) * $perPage;
}

$sql = 'SELECT p.*, c.name AS category_name, b.name AS brand_name, u.full_name AS seller_name,
               sv.verification_status ' . $fromSql . '
        ORDER BY p.created_at DESC
        LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>
<?php
$filterFormAction = site_url('pages/discovery.php');
$filterResetUrl = site_url('pages/discovery.php');
$filterPanelTitle = 'Filters';
$activeFilterChips = active_filter_chips(
    'pages/discovery.php',
    $search,
    (string) $categoryId,
    (string) $brandId,
    (string) $priceMin,
    (string) $priceMax,
    $categories,
    $brands
);
$activeFilterCount = count($activeFilterChips);

$pageTitle = 'Discover — Ubuntu Market';
$bodyClass = 'discover-page has-filters';
include __DIR__ . '/../includes/header.php';
?>
  <div class="page-container">
    <div class="page-top">
      <div>
        <h1 class="section-title page-heading">Discover</h1>
        <p class="page-subtitle">Find the best local listings — filter by category, brand or price.</p>
      </div>
      <div class="page-actions">
        <button type="button" id="filterToggle" class="filter-toggle-btn" aria-expanded="false" aria-controls="filterPanel">
          Filters
          <?php if ($activeFilterCount > 0): ?>
            <span class="filter-toggle-badge"><?= $activeFilterCount ?></span>
          <?php endif; ?>
        </button>
      </div>
    </div>

    <div class="page-layout has-filters">
      <?php include __DIR__ . '/../includes/filter-panel.php'; ?>

      <main class="discover-main">
        <?php if ($activeFilterCount > 0): ?>
          <div class="active-filters-bar" aria-label="Active filters">
            <span class="active-filters-label">Showing</span>
            <?php foreach ($activeFilterChips as $chip): ?>
              <a href="<?= htmlspecialchars($chip['href']) ?>" class="filter-chip">
                <?= htmlspecialchars($chip['label']) ?>
                <span class="filter-chip-remove" aria-hidden="true">×</span>
              </a>
            <?php endforeach; ?>
            <a href="<?= htmlspecialchars($filterResetUrl) ?>" class="filter-chip filter-chip--clear">Clear all</a>
          </div>
        <?php endif; ?>
        <?php if ($totalProducts > 0): ?>
          <p class="results-meta"><?= (int) $totalProducts ?> product<?= $totalProducts === 1 ? '' : 's' ?><?= $totalPages > 1 ? ' · page ' . $currentPage . ' of ' . $totalPages : '' ?></p>
        <?php endif; ?>
        <?php if (count($products) === 0): ?>
          <div class="empty-state">
            <h3>No products found</h3>
            <p>Try a different keyword or remove some filters.</p>
          </div>
        <?php else: ?>
          <div class="products-grid">
            <?php foreach ($products as $product): ?>
              <?php
                $pid = (int) $product['product_id'];
                $inFav = in_array($pid, $favoriteIds, true);
              ?>
              <div class="product-card">
                <a href="<?= htmlspecialchars(site_url('pages/product-view.php', ['product_id' => $pid])) ?>" class="product-card-link">
                  <div class="product-img-wrap">
                    <?php if (($product['verification_status'] ?? '') === 'approved'): ?>
                      <span class="verified-badge">✔ Verified</span>
                    <?php endif; ?>
                    <img src="<?= htmlspecialchars(asset_url($product['image_url'] ?? '')) ?>" alt="<?= htmlspecialchars($product['title']) ?>" loading="lazy" onerror="this.onerror=null;this.src='<?= htmlspecialchars(asset_url(''), ENT_QUOTES) ?>'">
                  </div>
                  <div class="product-card-body">
                    <p class="product-name"><?= htmlspecialchars($product['title']) ?></p>
                    <p class="product-meta"><?= htmlspecialchars($product['brand_name'] ?? $product['category_name']) ?></p>
                    <p class="product-price">R <?= number_format($product['price'], 2) ?></p>
                    <p class="product-shop"><?= htmlspecialchars($product['seller_name']) ?></p>
                  </div>
                </a>
                <a href="<?= htmlspecialchars(site_url('pages/toggle-wishlist.php', ['product_id' => $pid, 'redirect' => 'discovery.php'])) ?>" class="wishlist-btn wishlist-corner<?= $inFav ? ' active' : '' ?>" aria-label="<?= $inFav ? 'Remove from favorites' : 'Add to favorites' ?>"><?= $inFav ? '♥' : '♡' ?></a>
                <form method="post" action="<?= htmlspecialchars(site_url('pages/cart.php')) ?>" class="product-card-actions">
                  <input type="hidden" name="action" value="add">
                  <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                  <input type="hidden" name="quantity" value="1">
                  <button type="submit" class="add-cart-btn">Add to cart</button>
                </form>
              </div>
            <?php endforeach; ?>
          </div>
          <?php if ($totalPages > 1): ?>
            <nav class="pagination-bar" aria-label="Product pages">
              <?php if ($currentPage > 1): ?>
                <a class="secondary-btn" href="<?= htmlspecialchars(site_url('pages/discovery.php', pagination_query(['page' => $currentPage - 1]))) ?>">← Previous</a>
              <?php endif; ?>
              <span class="pagination-status">Page <?= $currentPage ?> of <?= $totalPages ?></span>
              <?php if ($currentPage < $totalPages): ?>
                <a class="secondary-btn" href="<?= htmlspecialchars(site_url('pages/discovery.php', pagination_query(['page' => $currentPage + 1]))) ?>">Next →</a>
              <?php endif; ?>
            </nav>
          <?php endif; ?>
        <?php endif; ?>
      </main>
    </div>
  </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

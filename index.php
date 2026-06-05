<?php
// homepage - hero banner, brands row and trending products
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/reviews.php';

$favoriteIds = $_SESSION['favorites'] ?? [];
if (!is_array($favoriteIds)) {
    $favoriteIds = [];
}

$pageTitle = 'Ubuntu Market';
$bodyClass = 'home-page';

// each hero slide sends the user to Discover with a search term
$heroSlides = [
    ['image' => 'images/hero slide/Hero slide 1.png', 'alt' => 'Tech deals — save up to 80%', 'search' => 'Electronics'],
    ['image' => 'images/hero slide/Hero slide 2.png', 'alt' => 'Clothing deals — save up to 90%', 'search' => 'Fashion'],
    ['image' => 'images/hero slide/Hero slide 3.png', 'alt' => 'Home essentials deals', 'search' => 'Home'],
];
$heroSlidesData = [];
foreach ($heroSlides as $slide) {
    $heroSlidesData[] = [
        'image' => asset_url($slide['image']),
        'alt' => $slide['alt'],
        'href' => site_url('pages/discovery.php', ['search' => $slide['search']]),
    ];
}
$heroSlideCount = count($heroSlidesData);

include __DIR__ . '/includes/header.php';
?>

<section
  class="hero-carousel"
  id="heroCarousel"
  aria-roledescription="carousel"
  aria-label="Featured deals"
  data-autoplay="true"
  data-interval="5500"
  data-slide-count="<?= (int) $heroSlideCount ?>"
  tabindex="0"
>
  <div class="hero-carousel-wrap">
    <button type="button" class="hero-arrow hero-arrow--prev" id="heroPrev" aria-label="Previous slide">&#8249;</button>

    <div class="hero-carousel-frame" id="heroCarouselFrame">
      <div class="hero-carousel-track" id="heroTrack" style="--hero-index: 0;">
        <?php foreach ($heroSlidesData as $i => $slide): ?>
          <div
            class="hero-slide<?= $i === 0 ? ' is-active' : '' ?>"
            role="group"
            aria-roledescription="slide"
            aria-label="<?= ($i + 1) . ' of ' . $heroSlideCount ?>"
            aria-hidden="<?= $i === 0 ? 'false' : 'true' ?>"
            data-index="<?= $i ?>"
          >
            <a href="<?= htmlspecialchars($slide['href']) ?>" class="hero-slide-link" tabindex="<?= $i === 0 ? '0' : '-1' ?>">
              <img
                src="<?= htmlspecialchars($slide['image']) ?>"
                alt="<?= htmlspecialchars($slide['alt']) ?>"
                width="1280"
                height="420"
                <?= $i === 0 ? 'decoding="async" fetchpriority="high"' : 'loading="lazy"' ?>
              >
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <button type="button" class="hero-arrow hero-arrow--next" id="heroNext" aria-label="Next slide">&#8250;</button>

    <?php if ($heroSlideCount > 1): ?>
    <div class="hero-dots" role="tablist" aria-label="Choose a slide">
      <?php foreach ($heroSlidesData as $i => $slide): ?>
        <button
          type="button"
          class="hero-dot<?= $i === 0 ? ' active' : '' ?>"
          role="tab"
          aria-selected="<?= $i === 0 ? 'true' : 'false' ?>"
          aria-controls="heroCarousel"
          aria-label="<?= htmlspecialchars($slide['alt']) ?>"
          data-index="<?= $i ?>"
        ></button>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<section class="brands-section" aria-labelledby="brands-heading">
  <div class="brands-section-inner">
    <div class="brands-header">
      <h2 class="section-title" id="brands-heading">Shop by Brand</h2>
      <a href="<?= site_url('pages/discovery.php') ?>" class="see-more-link">See more</a>
    </div>

    <div class="brands-carousel" id="brandsCarousel">
      <button type="button" class="brands-nav brands-nav--prev" id="brandsPrev" aria-label="Scroll brands left">‹</button>

      <div class="brands-viewport" id="brandsViewport">
        <div class="brands-track" id="brandsTrack">
          <?php
            $stmt = $pdo->query('SELECT brand_id, name, logo_url FROM brands ORDER BY name ASC');
            $brands = $stmt->fetchAll();
            foreach ($brands as $brand):
          ?>
            <a
              href="<?= site_url('pages/discovery.php', ['brand_id' => $brand['brand_id']]) ?>"
              class="brand-pill"
            >
              <?php if (!empty($brand['logo_url'])): ?>
                <img
                  src="<?= htmlspecialchars(asset_url($brand['logo_url'])) ?>"
                  alt=""
                  width="24"
                  height="24"
                  loading="lazy"
                  onerror="this.style.display='none'"
                >
              <?php endif; ?>
              <span><?= htmlspecialchars($brand['name']) ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

      <button type="button" class="brands-nav brands-nav--next" id="brandsNext" aria-label="Scroll brands right">›</button>
    </div>

    <div class="brands-progress" id="brandsProgress" hidden>
      <div class="brands-progress-bar" id="brandsProgressBar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
        <span class="brands-progress-fill" id="brandsProgressFill"></span>
      </div>
    </div>
  </div>
</section>

<section class="products-section container">
  <div class="section-header">
    <div>
      <h2 class="section-title">Trending Products</h2>
      <p class="section-sub">Fresh listings from trusted local sellers</p>
    </div>
    <a href="<?= site_url('pages/discovery.php') ?>" class="view-all-btn">View All →</a>
  </div>
  <div class="products-grid">
  <?php
    $stmt = $pdo->query("
      SELECT p.*, u.full_name AS shop_name,
             sv.verification_status
      FROM products p
      JOIN users u ON p.seller_id = u.user_id
      LEFT JOIN seller_verification sv ON sv.user_id = p.seller_id
      WHERE p.status = 'active'
      ORDER BY p.created_at DESC
      LIMIT 12
    ");
    $products = $stmt->fetchAll();
    foreach ($products as $product):
      $pid = (int) $product['product_id'];
      $inFav = in_array($pid, $favoriteIds, true);
  ?>
    <div class="product-card">
      <a href="<?= site_url('pages/product-view.php', ['product_id' => $pid]) ?>" class="product-card-link">
        <div class="product-img-wrap">

          <?php if (($product['verification_status'] ?? '') === 'approved'): ?>
            <span class="verified-badge">✔ Verified</span>
          <?php endif; ?>

          <img src="<?= htmlspecialchars(asset_url($product['image_url'] ?? '')) ?>"
               alt="<?= htmlspecialchars($product['title']) ?>"
               loading="lazy"
               onerror="this.onerror=null;this.src='<?= htmlspecialchars(asset_url(''), ENT_QUOTES) ?>'">
        </div>

        <p class="product-name"><?= htmlspecialchars($product['title']) ?></p>
        <p class="product-rating"><?= htmlspecialchars(product_rating_label($pdo, $pid)) ?></p>
        <p class="product-price">R <?= number_format($product['price'], 2) ?></p>
        <p class="product-shop"><?= htmlspecialchars($product['shop_name']) ?></p>
      </a>
      <a href="<?= site_url('pages/toggle-wishlist.php', ['product_id' => $pid, 'redirect' => 'index.php']) ?>" class="wishlist-btn wishlist-corner<?= $inFav ? ' active' : '' ?>" aria-label="<?= $inFav ? 'Remove from favorites' : 'Add to favorites' ?>"><?= $inFav ? '♥' : '♡' ?></a>
    </div>
  <?php endforeach; ?>
</div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
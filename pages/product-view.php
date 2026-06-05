<?php
// single product page - cart, wishlist, reviews
require_once '../includes/db.php';
require_once '../includes/auth.php';

require_once '../includes/helpers.php';

require_once '../includes/reviews.php';



$productId = isset($_GET['product_id']) ? (int) $_GET['product_id'] : 0;

if ($productId <= 0) {

    header('Location: ' . site_url('pages/discovery.php'));

    exit;

}



if (!isset($_SESSION['favorites'])) {

    $_SESSION['favorites'] = [];

}



$reviewErrors = [];

$reviewSuccess = '';



if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';



    if ($action === 'submit_review') {

        requireLogin();

        $rating = (int) ($_POST['rating'] ?? 0);

        $comment = trim($_POST['comment'] ?? '');

        $reviewProductId = (int) ($_POST['product_id'] ?? 0);



        if ($reviewProductId !== $productId) {

            $reviewErrors[] = 'Invalid product.';

        } elseif ($rating < 1 || $rating > 5) {

            $reviewErrors[] = 'Please choose a rating from 1 to 5 stars.';

        } elseif ($comment === '') {

            $reviewErrors[] = 'Please write a short review comment.';

        } elseif (!buyer_can_review_product($pdo, (int) $_SESSION['user_id'], $productId)) {

            $reviewErrors[] = 'You can only review products you have purchased and paid for.';

        } else {

            $orderStmt = $pdo->prepare(

                "SELECT order_id FROM orders

                 WHERE buyer_id = ? AND product_id = ? AND payment_status = 'paid'

                 ORDER BY ordered_at DESC LIMIT 1"

            );

            $orderStmt->execute([$_SESSION['user_id'], $productId]);

            $orderRow = $orderStmt->fetch();



            $insert = $pdo->prepare(

                'INSERT INTO reviews (product_id, buyer_id, order_id, rating, comment, status)

                 VALUES (?, ?, ?, ?, ?, ?)'

            );

            $insert->execute([

                $productId,

                $_SESSION['user_id'],

                $orderRow['order_id'] ?? null,

                $rating,

                $comment,

                'pending',

            ]);

            $reviewSuccess = 'Thank you! Your review has been submitted and will appear after moderation.';

        }

    }



    if ($action === 'add_to_cart' && isset($_POST['product_id'])) {

        $productToAdd = (int) $_POST['product_id'];

        $quantity = max(1, (int) ($_POST['quantity'] ?? 1));

        if (!isset($_SESSION['cart'])) {

            $_SESSION['cart'] = [];

        }

        $stockStmt = $pdo->prepare('SELECT quantity FROM products WHERE product_id = ? AND status = ?');

        $stockStmt->execute([$productToAdd, 'active']);

        $stockRow = $stockStmt->fetch();

        $available = $stockRow ? max(0, (int) $stockRow['quantity']) : 0;

        if ($available < 1) {

            $_SESSION['product_notice'] = 'This item is out of stock.';

            header('Location: ' . site_url('pages/product-view.php', ['product_id' => $productToAdd]));

            exit;

        }

        $current = (int) ($_SESSION['cart'][$productToAdd] ?? 0);

        $_SESSION['cart'][$productToAdd] = min($available, $current + $quantity);

        header('Location: ' . site_url('pages/cart.php'));

        exit;

    }



    if ($action === 'toggle_favorite') {

        if (!in_array($productId, $_SESSION['favorites'], true)) {

            $_SESSION['favorites'][] = $productId;

        } else {

            $newFavs = [];
            foreach ($_SESSION['favorites'] as $id) {
                if ((int) $id !== $productId) {
                    $newFavs[] = $id;
                }
            }
            $_SESSION['favorites'] = $newFavs;

        }

        header('Location: ' . site_url('pages/product-view.php', ['product_id' => $productId]));

        exit;

    }



    if ($action !== 'submit_review') {

        header('Location: ' . site_url('pages/product-view.php', ['product_id' => $productId]));

        exit;

    }

}



$stmt = $pdo->prepare("SELECT p.*, c.name AS category_name, b.name AS brand_name, u.full_name AS seller_name, sv.verification_status

        FROM products p

        JOIN categories c ON p.category_id = c.category_id

        LEFT JOIN brands b ON p.brand_id = b.brand_id

        JOIN users u ON p.seller_id = u.user_id

        LEFT JOIN seller_verification sv ON sv.user_id = p.seller_id

        WHERE p.product_id = :product_id");

$stmt->execute([':product_id' => $productId]);

$product = $stmt->fetch();



if (!$product) {

    header('Location: ' . site_url('pages/discovery.php'));

    exit;

}



$isFavorite = in_array($productId, $_SESSION['favorites'], true);

$reviewStats = product_review_stats($pdo, $productId);

$reviewsList = product_reviews_list($pdo, $productId);

$canReview = isLoggedIn() && buyer_can_review_product($pdo, (int) $_SESSION['user_id'], $productId);

$existingReview = isLoggedIn() ? buyer_existing_review($pdo, (int) $_SESSION['user_id'], $productId) : null;



$productNotice = $_SESSION['product_notice'] ?? '';

unset($_SESSION['product_notice']);



$pageTitle = $product['title'] . ' — Ubuntu Market';

$bodyClass = 'product-detail-page';

include __DIR__ . '/../includes/header.php';

?>



  <div class="page-container">

    <?php if ($productNotice !== ''): ?>

      <div class="form-error"><?= htmlspecialchars($productNotice) ?></div>

    <?php endif; ?>

    <div class="page-top">

      <div>

        <a href="discovery.php" class="link-button">← Back to Discover</a>

        <h1><?= htmlspecialchars($product['title']) ?></h1>

        <p class="page-subtitle">Product details and seller information.</p>

      </div>

    </div>



    <div class="product-view-layout">

      <section class="product-view-image">

        <img src="<?= htmlspecialchars(asset_url($product['image_url'] ?? '')) ?>" alt="<?= htmlspecialchars($product['title']) ?>" width="640" height="640" decoding="async" fetchpriority="high" onerror="this.onerror=null;this.src='<?= htmlspecialchars(asset_url(''), ENT_QUOTES) ?>'">

      </section>



      <section class="product-view-details">

        <div class="product-view-meta">

          <span class="product-price-large">R <?= number_format($product['price'], 2) ?></span>

          <span class="product-badge"><?= htmlspecialchars($product['brand_name'] ?? $product['category_name']) ?></span>

          <?php if (($product['verification_status'] ?? '') === 'approved'): ?>

            <span class="verified-badge">Seller verified</span>

          <?php endif; ?>

        </div>



        <div class="product-review-summary">

          <?= render_star_rating($reviewStats['average'], $reviewStats['count']) ?>

          <span class="review-summary-text">

            <?= $reviewStats['count'] === 0 ? 'No reviews yet' : $reviewStats['average'] . '/5 · ' . $reviewStats['count'] . ' review' . ($reviewStats['count'] === 1 ? '' : 's') ?>

          </span>

        </div>



        <p class="product-description"><?= nl2br(htmlspecialchars($product['description'])) ?></p>



        <?php if ($product['status'] === 'active'): ?>

        <div class="product-action-row">

          <form method="post" class="product-quantity-form">

            <input type="hidden" name="action" value="add_to_cart">

            <input type="hidden" name="product_id" value="<?= $productId ?>">

            <label class="quantity-label">Qty

              <input type="number" name="quantity" value="1" min="1" max="<?= max(1, (int) $product['quantity']) ?>">

            </label>

            <button type="submit" class="primary-btn">Add to cart</button>

          </form>

          <form method="post" class="inline-form">

            <input type="hidden" name="action" value="toggle_favorite">

            <button type="submit" class="secondary-btn"><?= $isFavorite ? 'Remove favorite' : 'Save to favorites' ?></button>

          </form>

        </div>

        <?php else: ?>

          <p class="form-error">This listing is not available for purchase right now.</p>

        <?php endif; ?>



        <div class="product-detail-block">

          <h3>Seller</h3>

          <p><?= htmlspecialchars($product['seller_name']) ?></p>

        </div>

        <div class="product-detail-block two-column-block">

          <div>

            <h3>Category</h3>

            <p><?= htmlspecialchars($product['category_name']) ?></p>

          </div>

          <div>

            <h3>Stock available</h3>

            <p><?= max(0, (int) $product['quantity']) ?> in stock</p>

          </div>

        </div>

      </section>

    </div>



    <section class="product-reviews-section" id="reviews">

      <h2 class="section-title">Customer reviews</h2>



      <?php if ($reviewSuccess): ?>

        <div class="form-success"><?= htmlspecialchars($reviewSuccess) ?></div>

      <?php endif; ?>

      <?php if (!empty($reviewErrors)): ?>

        <div class="form-error">

          <ul><?php foreach ($reviewErrors as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?></ul>

        </div>

      <?php endif; ?>



      <?php if ($existingReview): ?>

        <div class="review-card review-card--own">

          <p class="review-card-meta"><strong>Your review</strong> · <?= render_star_rating((float) $existingReview['rating'], 1) ?>
            <?php if ($existingReview['status'] === 'pending'): ?>
              <span class="review-pending-badge">Awaiting approval</span>
            <?php endif; ?>
          </p>

          <p><?= nl2br(htmlspecialchars($existingReview['comment'])) ?></p>

          <p class="review-card-date"><?= date('M j, Y', strtotime($existingReview['created_at'])) ?></p>

        </div>

      <?php elseif ($canReview): ?>

        <form method="post" class="review-compose" id="reviewComposeForm" novalidate>

          <input type="hidden" name="action" value="submit_review">

          <input type="hidden" name="product_id" value="<?= $productId ?>">

          <div class="review-compose-header">

            <h3>Write a review</h3>

            <p>Help other buyers by sharing your honest experience with this product.</p>

          </div>

          <div class="review-form-field">

            <span class="review-form-label" id="ratingLabel">Your rating</span>

            <div class="star-rating-input" role="radiogroup" aria-labelledby="ratingLabel" data-rating-input>

              <?php for ($i = 1; $i <= 5; $i++): ?>

                <button type="button" class="star-rating-btn" data-value="<?= $i ?>" aria-label="<?= $i ?> out of 5 stars">

                  <span class="star-icon" aria-hidden="true">★</span>

                </button>

              <?php endfor; ?>

            </div>

            <input type="hidden" name="rating" id="reviewRating" value="" required>

            <p class="star-rating-hint" id="starRatingHint" aria-live="polite">Select a star rating</p>

          </div>

          <div class="review-form-field">

            <label class="review-form-label" for="comment">Your review</label>

            <textarea id="comment" name="comment" rows="5" maxlength="500" placeholder="What did you like or dislike? Would you recommend it?" required></textarea>

            <span class="review-char-count"><span id="reviewCharCount">0</span> / 500</span>

          </div>

          <button type="submit" class="review-submit-btn">Publish review</button>

        </form>

      <?php elseif (isLoggedIn()): ?>

        <p class="page-subtitle">Purchase this product to leave a review.</p>

      <?php else: ?>

        <p class="page-subtitle"><a href="<?= site_url('pages/login-page.php') ?>">Sign in</a> and purchase this product to leave a review.</p>

      <?php endif; ?>



      <?php if (count($reviewsList) === 0): ?>

        <div class="empty-state"><p>No reviews yet. Be the first to share your experience.</p></div>

      <?php else: ?>

        <div class="reviews-list">

          <?php foreach ($reviewsList as $review): ?>

            <article class="review-card">

              <div class="review-card-head">

                <strong><?= htmlspecialchars($review['buyer_name']) ?></strong>

                <?= render_star_rating((float) $review['rating'], 1) ?>

                <span class="review-card-date"><?= date('M j, Y', strtotime($review['created_at'])) ?></span>

              </div>

              <p><?= nl2br(htmlspecialchars($review['comment'])) ?></p>

            </article>

          <?php endforeach; ?>

        </div>

      <?php endif; ?>

    </section>

  </div>



<?php include __DIR__ . '/../includes/footer.php'; ?>


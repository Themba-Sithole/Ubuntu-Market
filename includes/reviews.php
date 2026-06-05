<?php
// review helpers for product pages and product cards

function product_review_stats($pdo, $productId)
{
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) AS review_count, COALESCE(AVG(rating), 0) AS avg_rating
         FROM reviews WHERE product_id = ? AND status = 'approved'"
    );
    $stmt->execute([$productId]);
    $row = $stmt->fetch() ?: [];

    return [
        'count' => (int) ($row['review_count'] ?? 0),
        'average' => round((float) ($row['avg_rating'] ?? 0), 1),
    ];
}

function product_reviews_list($pdo, $productId, $limit = 20)
{
    $stmt = $pdo->prepare(
        "SELECT r.review_id, r.rating, r.comment, r.created_at, u.full_name AS buyer_name
         FROM reviews r
         JOIN users u ON r.buyer_id = u.user_id
         WHERE r.product_id = ? AND r.status = 'approved'
         ORDER BY r.created_at DESC
         LIMIT ?"
    );
    $stmt->bindValue(1, $productId, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

// only let buyers review products they actually bought and paid for
function buyer_can_review_product($pdo, $buyerId, $productId)
{
    $paid = $pdo->prepare(
        "SELECT 1 FROM orders
         WHERE buyer_id = ? AND product_id = ? AND payment_status = 'paid' LIMIT 1"
    );
    $paid->execute([$buyerId, $productId]);
    if (!$paid->fetch()) {
        return false;
    }

    $existing = $pdo->prepare('SELECT 1 FROM reviews WHERE buyer_id = ? AND product_id = ? LIMIT 1');
    $existing->execute([$buyerId, $productId]);

    return !$existing->fetch();
}

function buyer_existing_review($pdo, $buyerId, $productId)
{
    $stmt = $pdo->prepare(
        'SELECT review_id, rating, comment, status, created_at FROM reviews
         WHERE buyer_id = ? AND product_id = ? LIMIT 1'
    );
    $stmt->execute([$buyerId, $productId]);
    $row = $stmt->fetch();

    return $row ?: null;
}

function render_star_rating($average, $count)
{
    $full = (int) round($average);
    $stars = str_repeat('★', min(5, max(0, $full))) . str_repeat('☆', 5 - min(5, max(0, $full)));
    $label = $count === 0 ? 'No reviews yet' : $average . ' out of 5 (' . $count . ' review' . ($count === 1 ? '' : 's') . ')';

    return '<span class="review-stars" aria-label="' . htmlspecialchars($label) . '">' . $stars . '</span>';
}

function seller_unread_notification_count($pdo, $sellerId)
{
    try {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM seller_notifications WHERE seller_id = ? AND is_read = 0');
        $stmt->execute([$sellerId]);
        return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}

function mark_seller_notifications_read($pdo, $sellerId)
{
    try {
        $pdo->prepare('UPDATE seller_notifications SET is_read = 1 WHERE seller_id = ? AND is_read = 0')
            ->execute([$sellerId]);
    } catch (PDOException $e) {
    }
}

function product_rating_label($pdo, $productId): string
{
    $stats = product_review_stats($pdo, $productId);
    if ($stats['count'] === 0) {
        return 'No reviews yet';
    }

    return '★ ' . number_format($stats['average'], 1);
}

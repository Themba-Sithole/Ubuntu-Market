<?php
// creates extra tables and updates older versions of the database if needed

function ensure_schema($pdo)
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    ensure_reviews_schema($pdo);
    ensure_seller_notifications_schema($pdo);
}

function table_has_column($pdo, $table, $column)
{
    $stmt = $pdo->prepare(
        'SELECT 1 FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1'
    );
    $stmt->execute([$table, $column]);

    return (bool) $stmt->fetchColumn();
}

function table_exists($pdo, $table)
{
    $stmt = $pdo->prepare(
        'SELECT 1 FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? LIMIT 1'
    );
    $stmt->execute([$table]);

    return (bool) $stmt->fetchColumn();
}

function ensure_reviews_schema($pdo)
{
    if (!table_exists($pdo, 'reviews')) {
        $pdo->exec("
            CREATE TABLE reviews (
                review_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                product_id INT UNSIGNED NOT NULL,
                buyer_id INT UNSIGNED NOT NULL,
                order_id INT UNSIGNED NULL,
                rating TINYINT UNSIGNED NOT NULL,
                comment TEXT NULL,
                status ENUM('pending', 'approved', 'hidden') NOT NULL DEFAULT 'pending',
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_reviews_product (product_id),
                INDEX idx_reviews_buyer (buyer_id),
                INDEX idx_reviews_status (status),
                UNIQUE KEY uq_review_buyer_product (buyer_id, product_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        return;
    }

    if (!table_has_column($pdo, 'reviews', 'buyer_id')) {
        $pdo->exec('ALTER TABLE reviews ADD COLUMN buyer_id INT UNSIGNED NULL AFTER review_id');
        if (table_has_column($pdo, 'reviews', 'reviewer_id')) {
            $pdo->exec('UPDATE reviews SET buyer_id = reviewer_id WHERE buyer_id IS NULL');
        }
        $pdo->exec('ALTER TABLE reviews MODIFY buyer_id INT UNSIGNED NOT NULL');
    }

    if (!table_has_column($pdo, 'reviews', 'product_id')) {
        $pdo->exec('ALTER TABLE reviews ADD COLUMN product_id INT UNSIGNED NULL AFTER buyer_id');

        if (table_has_column($pdo, 'reviews', 'order_id')) {
            $pdo->exec(
                'UPDATE reviews r
                 INNER JOIN orders o ON r.order_id = o.order_id
                 SET r.product_id = o.product_id
                 WHERE r.product_id IS NULL'
            );
        }

        $pdo->exec('ALTER TABLE reviews MODIFY product_id INT UNSIGNED NOT NULL');
    }

    if (!table_has_column($pdo, 'reviews', 'status')) {
        $pdo->exec(
            "ALTER TABLE reviews ADD COLUMN status ENUM('pending', 'approved', 'hidden')
             NOT NULL DEFAULT 'approved' AFTER comment"
        );
        $pdo->exec("UPDATE reviews SET status = 'approved' WHERE status IS NULL OR status = ''");
    }

    if (!index_exists($pdo, 'reviews', 'uq_review_buyer_product')) {
        try {
            $pdo->exec('ALTER TABLE reviews ADD UNIQUE KEY uq_review_buyer_product (buyer_id, product_id)');
        } catch (PDOException $e) {
        }
    }
}

function index_exists($pdo, $table, $indexName)
{
    $stmt = $pdo->prepare(
        'SELECT 1 FROM information_schema.STATISTICS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ? LIMIT 1'
    );
    $stmt->execute([$table, $indexName]);

    return (bool) $stmt->fetchColumn();
}

function ensure_seller_notifications_schema($pdo)
{
    if (table_exists($pdo, 'seller_notifications')) {
        return;
    }

    $pdo->exec("
        CREATE TABLE seller_notifications (
            notification_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            seller_id INT UNSIGNED NOT NULL,
            order_id INT UNSIGNED NOT NULL,
            message VARCHAR(255) NOT NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_seller_notifications_seller (seller_id, is_read)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}

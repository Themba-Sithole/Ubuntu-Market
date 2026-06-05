<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

$productId = (int) ($_GET['product_id'] ?? 0);
$redirectKey = $_GET['redirect'] ?? 'discovery.php';

if ($productId <= 0) {
    header('Location: discovery.php');
    exit;
}

$check = $pdo->prepare("SELECT product_id FROM products WHERE product_id = ? AND status = 'active'");
$check->execute([$productId]);
if (!$check->fetch()) {
    header('Location: discovery.php');
    exit;
}

if (!isset($_SESSION['favorites']) || !is_array($_SESSION['favorites'])) {
    $_SESSION['favorites'] = [];
}

$ids = $_SESSION['favorites'];
if (in_array($productId, $ids, true)) {
    $_SESSION['favorites'] = array_values(array_filter($ids, function ($id) use ($productId) {
    return (int) $id !== $productId;
}));
} else {
    $ids[] = $productId;
    $_SESSION['favorites'] = array_values($ids);
}

switch ($redirectKey) {
    case 'index.php':
        header('Location: ../index.php');
        exit;
    case 'product-view.php':
        header('Location: product-view.php?product_id=' . $productId);
        exit;
    case 'shop.php':
    case 'favorites.php':
    case 'discovery.php':
        header('Location: ' . $redirectKey);
        exit;
    default:
        header('Location: discovery.php');
        exit;
}

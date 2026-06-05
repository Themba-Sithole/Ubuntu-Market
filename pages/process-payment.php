<?php
// creates pending orders then sends the buyer to PayFast checkout
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/payments.php';

requireLogin();

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['action'] ?? '') !== 'checkout') {
    header('Location: cart.php');
    exit;
}

$cartProductIds = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($cartProductIds), '?'));
$stmt = $pdo->prepare("SELECT product_id, title, price, quantity FROM products WHERE product_id IN ($placeholders) AND status = 'active'");
$stmt->execute($cartProductIds);
$products = $stmt->fetchAll();

$totalAmount = 0;
$orderItems = [];
foreach ($products as $product) {
    $quantity = (int) ($_SESSION['cart'][$product['product_id']] ?? 0);
    if ($quantity <= 0) {
        continue;
    }
    $available = max(0, (int) $product['quantity']);
    if ($available < 1 || $quantity > $available) {
        $_SESSION['cart_message'] = 'Your cart includes items that are no longer available in the requested quantity. Please review your cart.';
        header('Location: cart.php');
        exit;
    }
    $itemTotal = $product['price'] * $quantity;
    $totalAmount += $itemTotal;
    $orderItems[] = [
        'product_id' => $product['product_id'],
        'quantity' => $quantity,
        'total_amount' => $itemTotal,
        'title' => $product['title'],
    ];
}

if ($totalAmount <= 0 || empty($orderItems)) {
    header('Location: cart.php');
    exit;
}

$mPaymentId = uniqid('umkt_', true);
$itemName = 'Ubuntu Market Order ' . $mPaymentId;
$amount = number_format($totalAmount, 2, '.', '');

$pdo->beginTransaction();
try {
    $insertOrder = $pdo->prepare("INSERT INTO orders (buyer_id, product_id, quantity, total_amount, payment_status, delivery_status, escrow_status, payment_reference, ordered_at) VALUES (:buyer_id, :product_id, :quantity, :total_amount, 'pending', 'pending', 'holding', :payment_reference, NOW())");
    foreach ($orderItems as $item) {
        $insertOrder->execute([
            ':buyer_id' => $_SESSION['user_id'],
            ':product_id' => $item['product_id'],
            ':quantity' => $item['quantity'],
            ':total_amount' => $item['total_amount'],
            ':payment_reference' => $mPaymentId,
        ]);
    }
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    header('Location: cart.php');
    exit;
}

$_SESSION['last_payment_reference'] = $mPaymentId;

$data = [
    'merchant_id' => PAYFAST_MERCHANT_ID,
    'merchant_key' => PAYFAST_MERCHANT_KEY,
    'return_url' => PAYFAST_RETURN_URL,
    'cancel_url' => PAYFAST_CANCEL_URL,
    'notify_url' => PAYFAST_NOTIFY_URL,
    'name_first' => $_SESSION['full_name'] ?? 'Ubuntu',
    'email_address' => payfast_checkout_email((string) ($_SESSION['email'] ?? '')),
    'm_payment_id' => $mPaymentId,
    'amount' => $amount,
    'item_name' => $itemName,
];

$pfOutput = [];
foreach ($data as $key => $val) {
    if ($val !== '') {
        $pfOutput[] = $key . '=' . urlencode(trim($val));
    }
}

$signatureString = implode('&', $pfOutput);
if (PAYFAST_PASSPHRASE !== '') {
    $signatureString .= '&passphrase=' . urlencode(trim(PAYFAST_PASSPHRASE));
}

// PayFast needs an MD5 signature on the payment data
$signature = md5($signatureString);
$data['signature'] = $signature;

$query = http_build_query($data);

header('Location: ' . PAYFAST_SANDBOX_URL . '?' . $query);
exit;

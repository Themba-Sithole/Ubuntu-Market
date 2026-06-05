<?php
// PayFast server-to-server payment notification (ITN)
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/payments.php';

header('Content-Type: text/plain');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed';
    exit;
}

$pfData = $_POST;
if (empty($pfData) || !payfast_valid_signature($pfData)) {
    http_response_code(400);
    echo 'Invalid signature';
    exit;
}

if (!payfast_confirm_itn($pfData)) {
    http_response_code(400);
    echo 'ITN validation failed';
    exit;
}

$paymentStatus = $pfData['payment_status'] ?? '';
if (!in_array($paymentStatus, ['COMPLETE', 'COMPLETE_PENDING'], true)) {
    http_response_code(200);
    echo 'OK';
    exit;
}

$paymentReference = trim($pfData['m_payment_id'] ?? '');
if ($paymentReference === '') {
    http_response_code(400);
    echo 'Missing payment reference';
    exit;
}

mark_orders_paid($pdo, $paymentReference);

http_response_code(200);
echo 'OK';

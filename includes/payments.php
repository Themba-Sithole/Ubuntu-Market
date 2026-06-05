<?php
require_once __DIR__ . '/config.php';

function payfast_is_sandbox(): bool
{
    return str_contains(PAYFAST_SANDBOX_URL, 'sandbox.payfast.co.za');
}

// use a separate checkout email for PayFast test mode so payments don't get blocked
function payfast_checkout_email(string $buyerEmail): string
{
    if (payfast_is_sandbox()) {
        return env_or_default('PAYFAST_SANDBOX_BUYER_EMAIL', 'checkout@ubuntumarket.co.za');
    }

    $buyerEmail = trim($buyerEmail);

    return $buyerEmail !== '' ? $buyerEmail : 'checkout@ubuntumarket.co.za';
}

// called when PayFast confirms payment - marks orders paid and updates stock
function mark_orders_paid($pdo, $paymentReference, $buyerId = null)
{
    $paymentReference = trim($paymentReference);
    if ($paymentReference === '') {
        return false;
    }

    $sql = "SELECT order_id, buyer_id, product_id, quantity
            FROM orders
            WHERE payment_reference = :ref AND payment_status <> 'paid'";
    $params = [':ref' => $paymentReference];
    if ($buyerId !== null) {
        $sql .= ' AND buyer_id = :buyer_id';
        $params[':buyer_id'] = $buyerId;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
    if (empty($orders)) {
        return false;
    }

    $pdo->beginTransaction();
    try {
        $updateOrder = $pdo->prepare(
            "UPDATE orders SET payment_status = 'paid', paid_at = NOW()
             WHERE order_id = :order_id AND payment_status <> 'paid'"
        );
        $reduceProduct = $pdo->prepare(
            'UPDATE products SET quantity = GREATEST(quantity - :quantity, 0) WHERE product_id = :product_id'
        );
        $notifySeller = $pdo->prepare(
            'INSERT INTO seller_notifications (seller_id, order_id, message)
             SELECT p.seller_id, :order_id, :message FROM products p WHERE p.product_id = :product_id'
        );

        foreach ($orders as $order) {
            $updateOrder->execute([':order_id' => $order['order_id']]);
            if ($updateOrder->rowCount() < 1) {
                continue;
            }
            $reduceProduct->execute([
                ':quantity' => $order['quantity'],
                ':product_id' => $order['product_id'],
            ]);
            try {
                $notifySeller->execute([
                    ':order_id' => $order['order_id'],
                    ':product_id' => $order['product_id'],
                    ':message' => 'New paid order #' . $order['order_id'],
                ]);
            } catch (PDOException $e) {
                // notifications table might not exist on older databases
            }
        }

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

// checks the PayFast signature matches - from their integration docs
function payfast_valid_signature($pfData)
{
    if (empty($pfData['signature'])) {
        return false;
    }

    $signature = $pfData['signature'];
    unset($pfData['signature']);

    $parts = [];
    foreach ($pfData as $key => $val) {
        if ($val !== '' && $val !== null) {
            $parts[] = $key . '=' . urlencode(trim((string) $val));
        }
    }
    $paramString = implode('&', $parts);
    if (PAYFAST_PASSPHRASE !== '') {
        $paramString .= '&passphrase=' . urlencode(trim(PAYFAST_PASSPHRASE));
    }

    return md5($paramString) === $signature;
}

// double-check payment with PayFast servers before we trust the ITN
function payfast_confirm_itn($pfData)
{
    $hosts = ['www.payfast.co.za', 'sandbox.payfast.co.za'];
    $payload = http_build_query($pfData);

    foreach ($hosts as $host) {
        $url = 'https://' . $host . '/eng/query/validate';
        $ch = curl_init($url);
        if ($ch === false) {
            continue;
        }
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 30,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        if (trim((string) $response) === 'VALID') {
            return true;
        }
    }

    return false;
}

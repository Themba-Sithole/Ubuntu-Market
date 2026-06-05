<?php
require_once __DIR__ . '/helpers.php';

// PayFast settings - merchant details from my PayFast integration setup
function site_base_url(): string
{
    $configured = env_or_default('SITE_BASE_URL');
    if ($configured !== '') {
        return rtrim($configured, '/');
    }

    $httpHost = $_SERVER['HTTP_HOST'] ?? '';
    // still developing on XAMPP with the project in a subfolder
    if (
        str_starts_with($httpHost, 'localhost')
        || str_starts_with($httpHost, '127.0.0.1')
        || str_ends_with($httpHost, '.local')
        || str_ends_with($httpHost, '.test')
    ) {
        return 'http://localhost/Ubuntu%20Market';
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $scheme = $https ? 'https' : 'http';

    return $scheme . '://' . $httpHost;
}

define('PAYFAST_MERCHANT_ID', env_or_default('PAYFAST_MERCHANT_ID', '10048812'));
define('PAYFAST_MERCHANT_KEY', env_or_default('PAYFAST_MERCHANT_KEY', '4gfquw4jmiwkl'));
define('PAYFAST_PASSPHRASE', env_or_default('PAYFAST_PASSPHRASE', ''));
define('PAYFAST_SANDBOX_URL', env_or_default('PAYFAST_SANDBOX_URL', 'https://sandbox.payfast.co.za/eng/process'));

// return/cancel/notify urls must match the live site so PayFast can redirect back properly
$baseUrl = site_base_url();
define('PAYFAST_RETURN_URL', $baseUrl . '/pages/payment-return.php');
define('PAYFAST_CANCEL_URL', $baseUrl . '/pages/payment-cancel.php');
define('PAYFAST_NOTIFY_URL', $baseUrl . '/pages/payment-notify.php');

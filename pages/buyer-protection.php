<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

$pageTitle = 'Buyer Protection — Ubuntu Market';
$bodyClass = 'info-page';
$infoHeading = 'Buyer Protection';
$infoSubtitle = 'How Ubuntu Market helps protect your purchases.';
$infoSections = [
    [
        'title' => 'Secure checkout',
        'html' => '<p>Payments are processed through <strong>PayFast</strong>, a trusted South African payment gateway. Your card and banking details are handled by PayFast — not stored on our servers for checkout.</p>',
    ],
    [
        'title' => 'Order records',
        'html' => '<p>Every purchase creates an order record tied to your account. You can view payment status, delivery updates, and order references under <strong>My Orders</strong> at any time.</p>',
    ],
    [
        'title' => 'Verified sellers',
        'html' => '<p>Sellers can complete identity verification. Verified badges help you identify trusted shops. All listings are subject to moderation before or after going live.</p>',
    ],
    [
        'title' => 'Disputes & refunds',
        'html' => '<p>If an item is not received, is significantly not as described, or payment was taken incorrectly, contact <a href="mailto:support@ubuntumarket.co.za">support@ubuntumarket.co.za</a> within 7 days of delivery (or expected delivery date) with your order number.</p>
        <p>We review each case individually. Refunds, where approved, are processed according to our payment partner\'s timelines.</p>',
    ],
    [
        'title' => 'What we cannot guarantee',
        'html' => '<p>Ubuntu Market is a platform connecting buyers and sellers. We do not manufacture or warehouse products. Buyer protection covers platform processes — not independent agreements made outside our checkout.</p>',
    ],
];

require __DIR__ . '/../includes/info-page-shell.php';

<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

$pageTitle = 'Help Center — Ubuntu Market';
$bodyClass = 'info-page';
$infoHeading = 'Help Center';
$infoSubtitle = 'Answers to common questions about buying, selling, and managing your Ubuntu Market account.';
$infoSections = [
    [
        'title' => 'Getting started',
        'html' => '<p>Create a free account to buy or sell on Ubuntu Market. Choose <strong>Buy products</strong> or <strong>Buy &amp; sell</strong> when registering. Sellers can list items from the Seller Hub after signing in.</p>
        <ul><li><a href="' . htmlspecialchars(site_url('pages/register-page.php')) . '">Create an account</a></li><li><a href="' . htmlspecialchars(site_url('pages/discovery.php')) . '">Browse the marketplace</a></li><li><a href="' . htmlspecialchars(site_url('pages/add-item.php')) . '">Start selling</a></li></ul>',
    ],
    [
        'title' => 'Orders & payments',
        'html' => '<p>Checkout uses secure PayFast payment. After payment, your order appears under <strong>My Orders</strong> with payment and delivery status.</p>
        <ul><li>Track purchases from your account menu → My Orders</li><li>Sellers update delivery status when your item ships</li><li>Contact support if payment succeeded but the order is not confirmed</li></ul>',
    ],
    [
        'title' => 'Account & security',
        'html' => '<p>Keep your password private and use a strong, unique password. Sign out on shared devices. Update your profile from <strong>My Account</strong>.</p>
        <p>If you suspect unauthorised access, change your password immediately and email <a href="mailto:support@ubuntumarket.co.za">support@ubuntumarket.co.za</a>.</p>',
    ],
    [
        'title' => 'Reviews & listings',
        'html' => '<p>After a paid purchase you can leave a product review. Reviews are moderated before they appear publicly.</p>
        <p>New seller listings may require admin approval before going live. Check listing status in Seller Hub → My Products.</p>',
    ],
    [
        'title' => 'Contact support',
        'html' => '<p>Our team is here to help with orders, payments, and account issues.</p>
        <ul><li>Email: <a href="mailto:support@ubuntumarket.co.za">support@ubuntumarket.co.za</a></li><li>Phone: <a href="tel:+27623347216">+27 62 334 7216</a></li><li>Hours: Mon–Fri, 08:00–17:00 (SAST)</li></ul>',
    ],
];

require __DIR__ . '/../includes/info-page-shell.php';

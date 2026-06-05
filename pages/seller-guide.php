<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

$pageTitle = 'Seller Guide — Ubuntu Market';
$bodyClass = 'info-page';
$infoHeading = 'Seller Guide';
$infoSubtitle = 'Everything you need to start and grow your shop on Ubuntu Market.';
$infoSections = [
    [
        'title' => 'Become a seller',
        'html' => '<p>Register and select <strong>Buy &amp; sell products</strong>, or ask an admin to upgrade your account to seller. Access the <strong>Seller Hub</strong> from My Account after signing in.</p>
        <p><a href="' . htmlspecialchars(site_url('pages/register-page.php')) . '" class="link-button">Create seller account</a></p>',
    ],
    [
        'title' => 'Listing products',
        'html' => '<p>From Seller Hub → <strong>List New Item</strong>, add a title, description, category, price, stock quantity, and photo. New listings may be <em>pending</em> until approved by our team.</p>
        <p>Edit listings anytime from <strong>My Products</strong>. Major changes to live listings may require re-approval.</p>',
    ],
    [
        'title' => 'Orders & delivery',
        'html' => '<p>When a buyer pays, you receive a notification and the order appears in <strong>Orders</strong>. Update delivery status (processing → shipped → delivered) so buyers can track progress.</p>
        <p>Keep stock quantities accurate to avoid overselling.</p>',
    ],
    [
        'title' => 'Verification',
        'html' => '<p>Seller verification builds trust with buyers. Complete any verification steps requested after registration. Verified sellers may receive a badge on product pages.</p>',
    ],
    [
        'title' => 'Best practices',
        'html' => '<ul><li>Use clear photos and honest descriptions</li><li>Respond to orders promptly</li><li>Ship within stated timeframes</li><li>Follow our <a href="' . htmlspecialchars(site_url('pages/terms-of-service.php')) . '">Terms of Service</a></li></ul>
        <p><a href="' . htmlspecialchars(site_url('pages/add-item.php')) . '" class="primary-btn" style="display:inline-block;width:auto;margin-top:12px">List your first item</a></p>',
    ],
];

require __DIR__ . '/../includes/info-page-shell.php';

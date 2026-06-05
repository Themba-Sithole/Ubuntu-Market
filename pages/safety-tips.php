<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

$pageTitle = 'Safety Tips — Ubuntu Market';
$bodyClass = 'info-page';
$infoHeading = 'Safety Tips';
$infoSubtitle = 'Practical advice to shop and sell safely on South Africa\'s community marketplace.';
$infoSections = [
    [
        'title' => 'Pay through Ubuntu Market',
        'html' => '<p>Always complete purchases using our checkout and PayFast payment flow. Avoid paying sellers directly via EFT, cash deposit, or instant money transfer before you receive your item — these methods offer little recourse if something goes wrong.</p>',
    ],
    [
        'title' => 'Verify sellers',
        'html' => '<p>Look for verified seller badges and read product descriptions carefully. Check seller ratings and reviews when available. Be cautious of deals that seem too good to be true.</p>',
    ],
    [
        'title' => 'Protect your account',
        'html' => '<ul><li>Never share your password or one-time codes</li><li>Ubuntu Market staff will never ask for your password by email or phone</li><li>Use unique passwords and sign out on public computers</li><li>Report suspicious messages to support</li></ul>',
    ],
    [
        'title' => 'Meeting in person',
        'html' => '<p>If you collect an item locally, meet in a busy public place during daylight hours. Bring a friend when possible. Inspect the item before confirming receipt where applicable.</p>',
    ],
    [
        'title' => 'Report concerns',
        'html' => '<p>Report counterfeit items, harassment, or fraud to <a href="mailto:support@ubuntumarket.co.za">support@ubuntumarket.co.za</a> with your order reference and screenshots. We may suspend accounts that violate our policies.</p>',
    ],
];

require __DIR__ . '/../includes/info-page-shell.php';

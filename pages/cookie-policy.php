<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

$pageTitle = 'Cookie Policy — Ubuntu Market';
$bodyClass = 'info-page';
$infoHeading = 'Cookie Policy';
$infoSubtitle = 'How Ubuntu Market uses cookies and similar technologies.';
$infoSections = [
    [
        'title' => 'What are cookies?',
        'html' => '<p>Cookies are small text files stored on your device when you visit a website. They help the site remember your preferences and keep you signed in.</p>',
    ],
    [
        'title' => 'Cookies we use',
        'html' => '<ul><li><strong>Essential:</strong> session cookies to keep you logged in and maintain your cart</li><li><strong>Functional:</strong> preferences such as favourites stored in your session</li><li><strong>Performance:</strong> anonymous usage data to improve page load and stability (where enabled)</li></ul>',
    ],
    [
        'title' => 'Third parties',
        'html' => '<p>Payment partners such as PayFast may set their own cookies when you complete checkout on their pages. Review their privacy policies for details.</p>',
    ],
    [
        'title' => 'Managing cookies',
        'html' => '<p>You can block or delete cookies in your browser settings. Blocking essential cookies may prevent login, checkout, or cart features from working correctly.</p>',
    ],
    [
        'title' => 'Updates',
        'html' => '<p>We may update this policy. Material changes will be reflected on this page with an updated date.</p>',
    ],
];

require __DIR__ . '/../includes/info-page-shell.php';

<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

$pageTitle = 'Accessibility — Ubuntu Market';
$bodyClass = 'info-page';
$infoHeading = 'Accessibility';
$infoSubtitle = 'Our commitment to an inclusive shopping experience for everyone.';
$infoSections = [
    [
        'title' => 'Our commitment',
        'html' => '<p>Ubuntu Market aims to meet Web Content Accessibility Guidelines (WCAG) 2.1 Level AA where practicable. We continually improve navigation, contrast, and screen-reader support across the storefront and seller tools.</p>',
    ],
    [
        'title' => 'What we provide',
        'html' => '<ul><li>Semantic HTML landmarks (header, main, footer, navigation)</li><li>Keyboard-accessible menus, filters, and forms</li><li>Text alternatives for key images and icons</li><li>Visible focus states on interactive elements</li><li>Responsive layouts that work with zoom up to 200%</li></ul>',
    ],
    [
        'title' => 'Known limitations',
        'html' => '<p>Some third-party content (e.g. payment iframes) may not fully meet our standards. Older product images uploaded by sellers may lack detailed descriptions until updated.</p>',
    ],
    [
        'title' => 'Feedback',
        'html' => '<p>If you encounter a barrier using Ubuntu Market, please tell us so we can fix it. Email <a href="mailto:support@ubuntumarket.co.za">support@ubuntumarket.co.za</a> with the page URL and a description of the issue. We aim to respond within five business days.</p>',
    ],
];

require __DIR__ . '/../includes/info-page-shell.php';

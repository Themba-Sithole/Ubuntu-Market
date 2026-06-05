<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

$pageTitle = 'Privacy Policy — Ubuntu Market';
$bodyClass = 'info-page';
$infoHeading = 'Privacy Policy';
$infoSubtitle = 'How we collect, use, and protect your personal information.';
$infoSections = [
    [
        'title' => 'Who we are',
        'html' => '<p>Ubuntu Market operates a consumer-to-consumer marketplace in South Africa. For privacy enquiries contact <a href="mailto:support@ubuntumarket.co.za">support@ubuntumarket.co.za</a>.</p>',
    ],
    [
        'title' => 'Information we collect',
        'html' => '<ul><li><strong>Account data:</strong> name, email, phone, password (stored hashed)</li><li><strong>Transaction data:</strong> orders, payment references, delivery status</li><li><strong>Listing data:</strong> product details and images you upload</li><li><strong>Technical data:</strong> IP address, browser type, cookies (see Cookie Policy)</li></ul>',
    ],
    [
        'title' => 'How we use information',
        'html' => '<p>We use your data to operate the marketplace, process payments, prevent fraud, provide support, improve our services, and comply with legal obligations. We do not sell your personal information to third parties for marketing.</p>',
    ],
    [
        'title' => 'Sharing',
        'html' => '<p>We share data with service providers (e.g. payment processors, hosting) only as needed to run the Platform. Order details are shared between buyer and seller to fulfil transactions. We may disclose information if required by law.</p>',
    ],
    [
        'title' => 'Your rights (POPIA)',
        'html' => '<p>Under the Protection of Personal Information Act (POPIA), you may request access, correction, or deletion of your personal information, subject to legal retention requirements. Email <a href="mailto:support@ubuntumarket.co.za">support@ubuntumarket.co.za</a> to exercise your rights.</p>',
    ],
    [
        'title' => 'Retention & security',
        'html' => '<p>We retain data while your account is active and as required for legal, tax, or dispute purposes. We use reasonable technical and organisational measures to protect data; no online system is 100% secure.</p>',
    ],
];

require __DIR__ . '/../includes/info-page-shell.php';

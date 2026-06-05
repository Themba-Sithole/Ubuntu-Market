<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

$pageTitle = 'Terms of Service — Ubuntu Market';
$bodyClass = 'info-page';
$infoHeading = 'Terms of Service';
$infoSubtitle = 'Please read these terms carefully before using Ubuntu Market.';
$infoSections = [
    [
        'title' => 'Agreement',
        'html' => '<p>By accessing or using Ubuntu Market ("the Platform"), you agree to these Terms of Service. If you do not agree, do not use the Platform. We may update these terms; continued use after changes constitutes acceptance.</p>',
    ],
    [
        'title' => 'Accounts',
        'html' => '<p>You must provide accurate registration information and keep your credentials secure. You are responsible for activity under your account. We may suspend or terminate accounts that violate these terms or applicable law.</p>',
    ],
    [
        'title' => 'Buying & selling',
        'html' => '<p>Ubuntu Market provides a venue for buyers and sellers to transact. We are not a party to the sale contract between users except as the platform operator. Sellers are responsible for listing accuracy, legality of items, and fulfilment.</p>
        <p>Buyers must pay through the Platform checkout unless we expressly authorise otherwise.</p>',
    ],
    [
        'title' => 'Prohibited conduct',
        'html' => '<ul><li>Illegal, counterfeit, or stolen goods</li><li>Fraud, harassment, or misleading listings</li><li>Circumventing fees or payment systems</li><li>Scraping or automated abuse of the Platform</li><li>Infringing intellectual property rights</li></ul>',
    ],
    [
        'title' => 'Fees & payments',
        'html' => '<p>Payments are processed via third-party providers (e.g. PayFast). We may introduce platform fees with notice. Refund policies are described in our Buyer Protection guidelines and applied case by case.</p>',
    ],
    [
        'title' => 'Limitation of liability',
        'html' => '<p>To the fullest extent permitted by South African law, Ubuntu Market is not liable for indirect, incidental, or consequential damages arising from use of the Platform. Our total liability for a claim is limited to fees paid to us by you in the twelve months preceding the claim, where applicable.</p>',
    ],
    [
        'title' => 'Governing law',
        'html' => '<p>These terms are governed by the laws of the Republic of South Africa. Disputes are subject to the exclusive jurisdiction of South African courts, unless mandatory consumer protections require otherwise.</p>',
    ],
];

require __DIR__ . '/../includes/info-page-shell.php';

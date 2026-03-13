<?php
/**
 * Legal Page Generator — Quickstart
 *
 * Edit the $config array below with your site details, then run:
 *   php generate.php
 *
 * Generated files will be saved to the ./generated/ directory.
 */

require __DIR__ . '/../vendor/autoload.php';

use Yohns\Gens\Legal\LegalPageGenerator;

// ── Edit these values ─────────────────────────────────────────────
$websiteType = 'personal'; // 'personal', 'ecommerce', or 'social'

$config = [
    'company' => [
        'name'    => 'Your Company Name',
        'email'   => 'contact@example.com',
        'phone'   => '+1-555-0100',
        'address' => '123 Main Street',
        'country' => 'United States',
    ],
    'website' => [
        'url'  => 'https://example.com',
        'name' => 'Your Site Name',
    ],
];
// ── End config ────────────────────────────────────────────────────

$outputDir = __DIR__ . '/generated';

$pages = ['privacy-policy', 'terms-of-service', 'cookie-policy'];

foreach ($pages as $pageType) {
    $gen = new LegalPageGenerator($pageType, $websiteType, $config);

    // Save Markdown
    $gen->savePage($gen->generate(), "$pageType.md", $outputDir);

    // Save Bootstrap HTML
    $gen->savePage($gen->convertToHtml(full: true), "$pageType.html", $outputDir);

    echo "  [OK] $pageType.md + $pageType.html\n";
}

echo "\nDone! Files saved to: $outputDir/\n";

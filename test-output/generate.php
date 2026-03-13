<?php
/**
 * Legal Page Generator — Quickstart
 *
 * 1. Edit config/legal-pages.php with your site details and placeholder values.
 * 2. Choose your website type and pages below.
 * 3. Run:  php generate.php
 *
 * Generated files will be saved to the ./generated/ directory.
 * Set $outputDir to false to skip file saving and use the returned content directly.
 */

require __DIR__ . '/../vendor/autoload.php';

use Yohns\Gens\Legal\LegalPageGenerator;

// ── Configuration ────────────────────────────────────────────────
// All placeholder values are in config/legal-pages.php — edit there.
$config = require __DIR__ . '/config/legal-pages.php';

// ── Website Type ─────────────────────────────────────────────────
// Choose: 'personal', 'ecommerce', or 'social'
$websiteType = 'personal';

// ── Output Directory ─────────────────────────────────────────────
// Set to false to skip saving files (content returned in $results array).
$outputDir = __DIR__ . '/generated';

// ── Pages to Generate ────────────────────────────────────────────
// Uncomment the pages you want to generate.
$pages = [
    // Base (available for all website types)
    'privacy-policy',
    'terms-of-service',
    'cookie-policy',
    // 'dmca-policy',
    // 'accessibility-statement',

    // Personal
    // 'blog-disclaimer',

    // E-commerce
    // 'refund-policy',
    // 'shipping-policy',

    // Social
    // 'content-policy',
];
// ── End config ───────────────────────────────────────────────────

$results = [];

foreach ($pages as $pageType) {
    $gen = new LegalPageGenerator($pageType, $websiteType, $config);

    $markdown = $gen->generate();
    $html     = $gen->convertToHtml(full: true);

    if ($outputDir) {
        $gen->savePage($markdown, "$pageType.md", $outputDir);
        $gen->savePage($html, "$pageType.html", $outputDir);
        echo "  [OK] $pageType.md + $pageType.html\n";
    }

    // Content is always available in $results regardless of $outputDir
    $results[$pageType] = [
        'markdown' => $markdown,
        'html'     => $html,
    ];
}

if ($outputDir) {
    echo "\nDone! Files saved to: $outputDir/\n";
}

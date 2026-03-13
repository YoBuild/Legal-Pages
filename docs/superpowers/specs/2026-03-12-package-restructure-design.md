# Legal Pages Package Restructure

**Date:** 2026-03-12
**Status:** Draft

## Goal

Transform this project from a copy-pasted website scaffold into a standalone, installable Composer package (`yohns/legal-pages`) that generates legal pages as Markdown or Bootstrap 5.3.8 HTML.

## Changes

### 1. Composer Package Identity

**File:** `composer.json`

- **name:** `yohns/legal-pages`
- **description:** "Generate customizable legal pages (Privacy Policy, Terms of Service, etc.) from Markdown templates with placeholder substitution — outputs Markdown or Bootstrap 5.3.8 HTML"
- **require:** only `yohns/config` (^1.2) and `league/commonmark` (^2.7)
- **require-dev:** retain `phpunit/phpunit` (^12.4)
- **Remove from require:** guzzle, stripe, intervention/image, phpmailer, flysystem, plates, monolog, phpdotenv, crawler-detect, seo-manager, illuminate/support

### 2. Namespace Fix: Y0hn → Yohns

The namespace `Y0hn` uses a zero instead of the letter 'o'. Fix across the entire codebase.

**Note:** The `use Yohns\Core\Config` import already uses the correct `Yohns` spelling — only `namespace Y0hn\...` declarations and `use Y0hn\...` references between the Legal classes need updating.

**Directory rename:**
- `Y0hn/Gens/Legal/` → `Yohns/Gens/Legal/`

**PSR-4 autoload mapping:**
```json
{
    "Yohns\\": "Yohns/"
}
```

**Files requiring namespace/use statement updates:**
- `Yohns/Gens/Legal/LegalPageGenerator.php`
- `Yohns/Gens/Legal/LegalPageController.php`
- `Yohns/Gens/Legal/LegalPageConfig.php`
- `Yohns/Gens/Legal/LegalPageTemplate.php`
- `Yohns/Gens/Legal/LegalPageForm.php`
- `Yohns/Gens/Legal/LegalContentPresets.php`
- `ajax/ajax_legal_handler.php`
- `scripts/preview.php`
- `scripts/test-init.php`
- `tests/Unit/LegalPageGeneratorTest.php`
- `CLAUDE.md` (namespace references in documentation)

### 3. Bootstrap 5.3.8 HTML Output

**Current behavior:** `convertToHtml()` uses `league/commonmark` to produce plain HTML from Markdown.

**New behavior:** `convertToHtml()` produces HTML with Bootstrap 5.3.8 CSS classes on elements.

#### API

**Method signature:** `public function convertToHtml(bool $full = false): string`

```php
// Returns Bootstrap-classed HTML fragment (no <html>, <head>, <body>)
$html = $gen->convertToHtml();

// Returns full HTML page with Bootstrap 5.3.8 CDN in <head>
$html = $gen->convertToHtml(full: true);

// Raw Markdown output (unchanged)
$markdown = $gen->generate();
```

#### Bootstrap Class Mapping

Post-process CommonMark HTML output using DOMDocument to add Bootstrap classes. DOMDocument is preferred over regex because CommonMark can produce nested lists, tables with `<thead>`/`<tbody>`, etc.

| Element | Bootstrap Classes |
|---------|------------------|
| `<table>` | `table table-striped` |
| `<h1>` - `<h6>` | `mb-3` |
| `<ul>`, `<ol>` | `ps-3` (preserve native bullets/numbers — legal docs rely on numbered lists for clauses) |
| `<blockquote>` | `blockquote ps-3 border-start border-4` |
| `<a>` | `link-primary` |
| `<p>` | `mb-2` |
| `<hr>` | `my-4` |

#### Full Page Template

When `full: true`, wrap content in a full HTML page. The `<title>` is derived from `$this->formatName($this->pageType)` (e.g., "Privacy Policy"):

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        <!-- generated content here -->
    </div>
</body>
</html>
```

### 4. Config Integration

- `yohns/config` remains a hard dependency
- `loadCommonSubstitutions()` continues using `Config::get()` with try/catch fallback
- Config files that use `Config::get()` internally (e.g., schema defaults pulling `Config::get('site_name')`) work naturally since the Config class resolves them at load time

### 5. Usage Example (Post-Restructure)

```php
<?php
require 'vendor/autoload.php';

use Yohns\Gens\Legal\LegalPageGenerator;

$gen = new LegalPageGenerator('privacy-policy', 'ecommerce', [
    'company' => ['name' => 'Acme Inc', 'email' => 'legal@acme.com'],
    'website' => ['url' => 'https://acme.com', 'name' => 'Acme Store'],
]);

// Get raw Markdown
$markdown = $gen->generate();
file_put_contents('privacy-policy.md', $markdown);

// Get Bootstrap HTML fragment (for embedding in existing page)
$fragment = $gen->convertToHtml();

// Get full standalone HTML page with Bootstrap CDN
$fullPage = $gen->convertToHtml(full: true);
file_put_contents('privacy-policy.html', $fullPage);
```

## Out of Scope

- No changes to the frontend UI (`legal-generator.html`, `js/legal-generator.js`)
- No changes to template content (Markdown files in `legal/`)
- No changes to the AJAX handler behavior (just namespace fix)
- No new template placeholder syntax

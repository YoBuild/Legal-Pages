# Legal Pages Package Restructure — Implementation Plan

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Transform the project into a standalone Composer package (`yohns/legal-pages`) with corrected namespace and Bootstrap 5.3.8 HTML output.

**Architecture:** Fix namespace `Y0hn` → `Yohns`, trim composer.json to only required dependencies, and enhance `convertToHtml()` to post-process CommonMark output with Bootstrap classes via DOMDocument, with optional full-page wrapper.

**Tech Stack:** PHP 8.1+, league/commonmark, yohns/config, PHPUnit, DOMDocument

**Spec:** `docs/superpowers/specs/2026-03-12-package-restructure-design.md`

---

## Chunk 1: Namespace & Composer Fixes

### Task 1: Update composer.json

**Files:**
- Modify: `composer.json`

- [ ] **Step 1: Update composer.json fields**

Change name, description, and trim dependencies:

```json
{
    "name": "yohns/legal-pages",
    "description": "Generate customizable legal pages (Privacy Policy, Terms of Service, etc.) from Markdown templates with placeholder substitution — outputs Markdown or Bootstrap 5.3.8 HTML",
    "type": "library",
    "license": "MIT",
    "autoload": {
        "psr-4": {
            "Yohns\\": "Yohns/"
        }
    },
    "authors": [
        {
            "name": "Yohn",
            "email": "john.skem9@gmail.com"
        }
    ],
    "require": {
        "yohns/config": "^1.2",
        "league/commonmark": "^2.7"
    },
    "minimum-stability": "stable",
    "prefer-stable": true,
    "config": {
        "optimize-autoloader": true,
        "sort-packages": true
    },
    "require-dev": {
        "phpunit/phpunit": "^12.4"
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add composer.json
git commit -m "chore: update composer.json — rename to yohns/legal-pages, trim deps"
```

---

### Task 2: Rename directory Y0hn → Yohns

**Files:**
- Rename: `Y0hn/` → `Yohns/`

- [ ] **Step 1: Rename directory using git mv**

```bash
git mv Y0hn Yohns
```

- [ ] **Step 2: Commit**

```bash
git add -A
git commit -m "refactor: rename Y0hn directory to Yohns (fix zero→letter-o typo)"
```

---

### Task 3: Update namespace declarations in all PHP files

**Files:**
- Modify: `Yohns/Gens/Legal/LegalPageGenerator.php`
- Modify: `Yohns/Gens/Legal/LegalPageController.php`
- Modify: `Yohns/Gens/Legal/LegalPageConfig.php`
- Modify: `Yohns/Gens/Legal/LegalPageTemplate.php`
- Modify: `Yohns/Gens/Legal/LegalPageForm.php`
- Modify: `Yohns/Gens/Legal/LegalContentPresets.php`
- Modify: `ajax/ajax_legal_handler.php`
- Modify: `scripts/preview.php`
- Modify: `scripts/test-init.php`
- Modify: `tests/Unit/LegalPageGeneratorTest.php`

**Important:** The `use Yohns\Core\Config` import already has the correct spelling. Only update `namespace Y0hn\...` and `use Y0hn\...` references.

- [ ] **Step 1: Update namespace/use in all 6 library files**

In each file under `Yohns/Gens/Legal/`, replace:
- `namespace Y0hn\Gens\Legal;` → `namespace Yohns\Gens\Legal;`
- `@package Y0hn\Gens\Legal` → `@package Yohns\Gens\Legal`
- `// src/Y0hn/Gens/Legal/` → `// src/Yohns/Gens/Legal/`

Files: `LegalPageGenerator.php`, `LegalPageController.php`, `LegalPageConfig.php`, `LegalPageTemplate.php`, `LegalPageForm.php`, `LegalContentPresets.php`

Also in `LegalPageGenerator.php`, update the comment on line 61:
- `// Y0hn/Gens/Legal/ -> ../../legal` → `// Yohns/Gens/Legal/ -> ../../legal`

- [ ] **Step 2: Update use statements in consumer files**

In `ajax/ajax_legal_handler.php`:
- `// routes to unified Y0hn\Gens\Legal controller` → `// routes to unified Yohns\Gens\Legal controller`
- `use Y0hn\Gens\Legal\LegalPageController;` → `use Yohns\Gens\Legal\LegalPageController;`

In `scripts/preview.php`:
- `use Y0hn\Gens\Legal\LegalPageGenerator;` → `use Yohns\Gens\Legal\LegalPageGenerator;`
- `use Y0hn\Gens\Legal\LegalPageConfig;` → `use Yohns\Gens\Legal\LegalPageConfig;`

In `scripts/test-init.php`:
- `use Y0hn\Gens\Legal\LegalPageController;` → `use Yohns\Gens\Legal\LegalPageController;`

In `tests/Unit/LegalPageGeneratorTest.php`:
- `Tests for Y0hn\Gens\Legal\LegalPageGenerator` → `Tests for Yohns\Gens\Legal\LegalPageGenerator`
- `use Y0hn\Gens\Legal\LegalPageGenerator;` → `use Yohns\Gens\Legal\LegalPageGenerator;`

- [ ] **Step 3: Run tests to verify namespace change works**

Run: `vendor/bin/phpunit tests/Unit/LegalPageGeneratorTest.php`
Expected: All 6 tests pass

- [ ] **Step 4: Commit**

```bash
git add Yohns/Gens/Legal/ ajax/ajax_legal_handler.php scripts/preview.php scripts/test-init.php tests/Unit/LegalPageGeneratorTest.php
git commit -m "refactor: update namespace Y0hn to Yohns across all PHP files"
```

---

### Task 4: Update CLAUDE.md namespace references

**Files:**
- Modify: `CLAUDE.md`

- [ ] **Step 1: Replace all Y0hn references in CLAUDE.md**

Find and replace all occurrences of `Y0hn` with `Yohns` in `CLAUDE.md`. This includes:
- Directory paths like `Y0hn/Gens/Legal/`
- Namespace references like `Y0hn\Gens\Legal`
- PSR-4 mapping `"Y0hn\\"` → `"Yohns\\"`
- Code examples using `use Y0hn\...`

- [ ] **Step 2: Commit**

```bash
git add CLAUDE.md
git commit -m "docs: update CLAUDE.md with corrected Yohns namespace"
```

---

### Task 5: Regenerate autoloader

- [ ] **Step 1: Run composer dump-autoload**

```bash
composer dump-autoload
```

Expected: No errors, autoloader regenerated with `Yohns\\` mapping.

---

## Chunk 2: Bootstrap HTML Output

### Task 6: Write failing tests for Bootstrap HTML output

**Files:**
- Modify: `tests/Unit/LegalPageGeneratorTest.php`

- [ ] **Step 1: Add test for convertToHtml with Bootstrap classes (fragment)**

Add to `LegalPageGeneratorTest.php`:

```php
/**
 * Test that convertToHtml adds Bootstrap classes to elements
 */
public function testConvertToHtmlAddsBootstrapClasses()
{
    $gen = new LegalPageGenerator(
        'privacy-policy',
        'personal',
        ['company:name' => 'Test Co', 'website:url' => 'https://test.com']
    );

    $html = $gen->convertToHtml();

    // Should contain Bootstrap classes on elements
    // Headings get mb-3
    $this->assertMatchesRegularExpression('/<h[1-6][^>]*class="[^"]*mb-3[^"]*"/', $html);
    // Paragraphs get mb-2
    $this->assertStringContainsString('class="mb-2"', $html);
    // Should NOT contain full page wrapper
    $this->assertStringNotContainsString('<!DOCTYPE html>', $html);
    $this->assertStringNotContainsString('bootstrap.min.css', $html);
}
```

- [ ] **Step 2: Add test for convertToHtml with full page wrapper**

```php
/**
 * Test that convertToHtml(full: true) wraps in full HTML page with Bootstrap CDN
 */
public function testConvertToHtmlFullPage()
{
    $gen = new LegalPageGenerator(
        'privacy-policy',
        'personal',
        ['company:name' => 'Test Co', 'website:url' => 'https://test.com']
    );

    $html = $gen->convertToHtml(full: true);

    // Should have full page structure
    $this->assertStringContainsString('<!DOCTYPE html>', $html);
    $this->assertStringContainsString('bootstrap@5.3.8/dist/css/bootstrap.min.css', $html);
    $this->assertStringContainsString('<div class="container py-4">', $html);
    // Title should be derived from page type
    $this->assertStringContainsString('<title>Privacy Policy</title>', $html);
}
```

- [ ] **Step 3: Add test for Bootstrap table classes**

```php
/**
 * Test that tables get Bootstrap table classes
 */
public function testBootstrapTableClasses()
{
    $gen = new LegalPageGenerator('privacy-policy', 'personal', []);

    // Use reflection to test addBootstrapClasses directly
    $reflection = new \ReflectionClass($gen);
    $method = $reflection->getMethod('addBootstrapClasses');
    $method->setAccessible(true);

    $html = '<table><thead><tr><th>Header</th></tr></thead></table>';
    $result = $method->invoke($gen, $html);

    $this->assertStringContainsString('class="table table-striped"', $result);
}
```

- [ ] **Step 4: Run tests to verify they fail**

Run: `vendor/bin/phpunit tests/Unit/LegalPageGeneratorTest.php`
Expected: 3 new tests FAIL (method signature mismatch / `addBootstrapClasses` not found)

- [ ] **Step 5: Commit failing tests**

```bash
git add tests/Unit/LegalPageGeneratorTest.php
git commit -m "test: add failing tests for Bootstrap HTML output"
```

---

### Task 7: Implement addBootstrapClasses method

**Files:**
- Modify: `Yohns/Gens/Legal/LegalPageGenerator.php`

- [ ] **Step 1: Add the addBootstrapClasses private method**

Add this method to `LegalPageGenerator` class, after `convertToHtml()`:

```php
/**
 * Add Bootstrap 5.3.8 CSS classes to HTML elements using DOMDocument
 *
 * @param string $html Raw HTML from CommonMark
 * @return string HTML with Bootstrap classes added
 */
private function addBootstrapClasses(string $html): string
{
    if (empty(trim($html))) {
        return $html;
    }

    $doc = new \DOMDocument();
    // Suppress warnings for HTML5 tags, load as UTF-8
    @$doc->loadHTML(
        '<?xml encoding="UTF-8"><div id="bs-wrapper">' . $html . '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );

    $classMap = [
        'table'      => 'table table-striped',
        'blockquote' => 'blockquote ps-3 border-start border-4',
        'hr'         => 'my-4',
        'p'          => 'mb-2',
        'a'          => 'link-primary',
        'ul'         => 'ps-3',
        'ol'         => 'ps-3',
        'h1'         => 'mb-3',
        'h2'         => 'mb-3',
        'h3'         => 'mb-3',
        'h4'         => 'mb-3',
        'h5'         => 'mb-3',
        'h6'         => 'mb-3',
    ];

    foreach ($classMap as $tag => $classes) {
        $elements = $doc->getElementsByTagName($tag);
        foreach ($elements as $el) {
            $existing = $el->getAttribute('class');
            $el->setAttribute('class', $existing ? "$existing $classes" : $classes);
        }
    }

    // Extract inner HTML of wrapper div
    $wrapper = $doc->getElementById('bs-wrapper');
    $output = '';
    foreach ($wrapper->childNodes as $child) {
        $output .= $doc->saveHTML($child);
    }

    return $output;
}
```

- [ ] **Step 2: Run the table test to check addBootstrapClasses works**

Run: `vendor/bin/phpunit tests/Unit/LegalPageGeneratorTest.php --filter testBootstrapTableClasses`
Expected: PASS

- [ ] **Step 3: Commit**

```bash
git add Yohns/Gens/Legal/LegalPageGenerator.php
git commit -m "feat: add addBootstrapClasses method using DOMDocument"
```

---

### Task 8: Update convertToHtml to use Bootstrap classes and full-page wrapper

**Files:**
- Modify: `Yohns/Gens/Legal/LegalPageGenerator.php`

- [ ] **Step 1: Replace the existing convertToHtml method**

Replace the current `convertToHtml()` method (around line 425) with:

```php
/**
 * Convert generated markdown to HTML with Bootstrap 5.3.8 classes
 *
 * If the template was HTML, returns it as-is.
 *
 * @param bool $full If true, wrap in a full HTML page with Bootstrap CDN
 * @return string Generated HTML content with Bootstrap classes
 * @throws \Exception If conversion fails or template not found
 */
public function convertToHtml(bool $full = false): string
{
    // Generate the base content
    $content = $this->generate();

    // Check if we're working with a markdown template
    $templateFile = $this->findTemplate();
    if ($templateFile && pathinfo($templateFile, PATHINFO_EXTENSION) === 'html') {
        // Already HTML, return as-is
        return $content;
    }

    // Convert markdown to HTML using League\CommonMark
    $converter = new \League\CommonMark\CommonMarkConverter();
    $html = $converter->convert($content)->getContent();

    // Add Bootstrap classes to HTML elements
    $html = $this->addBootstrapClasses($html);

    if ($full) {
        $title = $this->formatName($this->pageType);
        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        {$html}
    </div>
</body>
</html>
HTML;
    }

    return $html;
}
```

- [ ] **Step 2: Run all tests**

Run: `vendor/bin/phpunit tests/Unit/LegalPageGeneratorTest.php`
Expected: All 10 tests pass (7 original + 3 new)

- [ ] **Step 3: Commit**

```bash
git add Yohns/Gens/Legal/LegalPageGenerator.php
git commit -m "feat: convertToHtml outputs Bootstrap 5.3.8 HTML with optional full-page wrapper"
```

---

### Task 9: Manual smoke test

- [ ] **Step 1: Run a quick generation test**

```bash
php -r "
require 'vendor/autoload.php';
use Yohns\Gens\Legal\LegalPageGenerator;
\$gen = new LegalPageGenerator('privacy-policy', 'personal', [
    'company:name' => 'Acme Inc',
    'website:url' => 'https://acme.com',
]);
echo '=== MARKDOWN ===\n';
echo substr(\$gen->generate(), 0, 200) . '\n\n';
echo '=== HTML FRAGMENT ===\n';
echo substr(\$gen->convertToHtml(), 0, 300) . '\n\n';
echo '=== FULL PAGE (first 500 chars) ===\n';
echo substr(\$gen->convertToHtml(full: true), 0, 500) . '\n';
"
```

Expected: Markdown output without HTML tags, HTML fragment with Bootstrap classes (e.g., `class="mb-3"` on headings), full page with `<!DOCTYPE html>` and Bootstrap CDN link.

- [ ] **Step 2: Final commit if any adjustments needed**

```bash
git add -A
git commit -m "chore: final adjustments after smoke test"
```

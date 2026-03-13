# CLI Publish Command

**Date:** 2026-03-13
**Status:** Draft

## Goal

Add a `php vendor/bin/legal-pages publish` command that copies starter files into the user's project so they can generate legal pages immediately after installing via composer.

## Command

```bash
php vendor/bin/legal-pages publish [--config] [--quickstart] [--ui] [--dir=PATH]
```

- No flags = publish all three (config, quickstart, ui)
- `--dir=PATH` = custom output directory (default: `legal-pages/` in project root)

## What Gets Published

### `--config`

Copies `config/legal-pages.php` → `{dir}/config/legal-pages.php`

The legal-pages config file with all placeholder keys organized by page type, compliance flags, and feature flags. User fills in their values.

### `--quickstart`

Copies a pre-built `generate.php` → `{dir}/generate.php`

A working PHP script that:
- Requires the composer autoloader
- Creates a `LegalPageGenerator` for each common page type
- Generates both markdown and full-page Bootstrap HTML
- Saves output to `{dir}/generated/`
- Has placeholder config values the user fills in (company name, email, etc.)

### `--ui`

Copies the web-based generator UI and its AJAX backend:
- `legal-generator.html` → `{dir}/legal-generator.html`
- `js/legal-generator.js` → `{dir}/js/legal-generator.js`
- `ajax/ajax_legal_handler.php` → `{dir}/ajax/ajax_legal_handler.php`

The published `ajax_legal_handler.php` requires `vendor/autoload.php` — the publish command adjusts the `require` path to be relative to the output directory (e.g., `__DIR__ . '/../../vendor/autoload.php'` for the default `legal-pages/ajax/` location).

**Important:** The source JS file uses absolute paths (`/ajax/ajax_legal_handler.php` with leading `/`). When publishing, the command must rewrite these to relative paths (`ajax/ajax_legal_handler.php`) so the UI works from any directory. This is a simple `str_replace('/ajax/ajax_legal_handler.php', 'ajax/ajax_legal_handler.php', ...)` during the copy.

## Default Output Structure

When no `--dir` is specified, files go to `legal-pages/` in the project root:

```
legal-pages/
  ajax/
    ajax_legal_handler.php
  config/
    legal-pages.php
  js/
    legal-generator.js
  generate.php
  legal-generator.html
```

## Implementation

### New Files

- `bin/legal-pages` — Composer bin entry point (thin PHP wrapper, parses args, calls PublishCommand)
- `Yohns/Gens/Legal/Console/PublishCommand.php` — Publish logic (copy files, create dirs, handle overwrites)

### composer.json Addition

```json
{
    "bin": ["bin/legal-pages"]
}
```

### Source Files for Copying

The publish command copies files from the package's own directory (resolved via `__DIR__` from the bin script or via composer's vendor path):

| Source (in package) | Destination | Flag |
|---------------------|-------------|------|
| `config/legal-pages.php` | `{dir}/config/legal-pages.php` | `--config` |
| `stubs/generate.php` | `{dir}/generate.php` | `--quickstart` |
| `legal-generator.html` | `{dir}/legal-generator.html` | `--ui` |
| `js/legal-generator.js` | `{dir}/js/legal-generator.js` | `--ui` |
| `ajax/ajax_legal_handler.php` | `{dir}/ajax/ajax_legal_handler.php` | `--ui` |

Note: The quickstart `generate.php` lives in a `stubs/` directory in the package since it's a template file, not a runnable part of the package itself. It must be created as part of this work.

### stubs/generate.php Content

A working PHP script the user can edit and run:

```php
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
```

### Behavior

- Creates directories as needed (`mkdir -p` equivalent)
- If a file already exists: prints a warning and skips it (does not overwrite by default)
- Prints each file copied on success
- Exit code 0 on success, 1 on error

### Console Output Example

```
$ php vendor/bin/legal-pages publish
Publishing legal-pages files to: legal-pages/

  [OK] config/legal-pages.php → legal-pages/config/legal-pages.php
  [OK] generate.php → legal-pages/generate.php
  [OK] legal-generator.html → legal-pages/legal-generator.html
  [OK] js/legal-generator.js → legal-pages/js/legal-generator.js

Done! Edit legal-pages/config/legal-pages.php with your site details, then run:
  php legal-pages/generate.php
```

### Error Cases

- Package source file not found → error message, exit 1
- Cannot create directory → error message, exit 1
- File already exists → skip with warning, continue (not an error)

## README Update

Restructure README.md so the **Getting Started** section is at the very top, right after the package description. It should show:

1. `composer require yohns/legal-pages`
2. `php vendor/bin/legal-pages publish`
3. Edit config, run `php legal-pages/generate.php`
4. Output in `legal-pages/generated/`

This three-step flow should be the first thing users see — install, publish, generate.

## Out of Scope

- No interactive prompts (no "enter your company name" wizard)
- No `--force` flag for overwriting (keep it simple, user can delete and re-publish)
- No unpublish/rollback command

# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Quick Start

**Install dependencies and run dev server:**
```bash
composer install
php -S localhost:8000 -t .
```

Then visit: http://localhost:8000/legal-generator.html

**Run tests:**
```bash
vendor/bin/phpunit tests/
# Or specific test:
vendor/bin/phpunit tests/Unit/LegalPageGeneratorTest.php
```

## Architecture Overview

This is a **legal page generator** that creates customizable Privacy Policies, Terms of Service, and other legal documents from Markdown templates with placeholder substitution.

### Request Flow
1. Frontend (`legal-generator.html` + `js/legal-generator.js`) →
2. AJAX endpoint (`ajax/ajax_legal_handler.php`) →
3. Controller (`Y0hn\Gens\Legal\LegalPageController`) →
4. Generator (`Y0hn\Gens\Legal\LegalPageGenerator`)

### Directory Structure
```
Y0hn/Gens/Legal/          # PHP library (PSR-4: Y0hn\ => Y0hn/)
  ├── LegalPageController.php    # AJAX request handler
  ├── LegalPageGenerator.php     # Core template processor
  ├── LegalPageTemplate.php      # Template discovery
  ├── LegalPageConfig.php        # Config integration
  ├── LegalContentPresets.php    # Default values by website type
  └── LegalPageForm.php          # Form state management

legal/                    # Markdown templates
  ├── base/               # Generic templates (fallback)
  ├── personal/           # Personal blog variants
  ├── ecommerce/          # E-commerce variants
  ├── social/             # Social network variants
  └── placeholders/       # Documentation

ajax/ajax_legal_handler.php     # AJAX entrypoint (POST only)
legal-generator.html             # Active frontend UI
js/legal-generator.js            # Frontend logic
config/                          # Site and legal config
tests/Unit/                      # PHPUnit tests
```

## Template System

### Placeholder Format
Templates use `{{category:field}}` syntax:
```markdown
{{company:name}} operates {{website:url}}
Last updated: {{current:date}}
```

Common categories: `company`, `website`, `data`, `ecommerce`, `social`, `compliance`, `current`, `feature`

See `legal/placeholders/cheat-sheet.md` for complete reference.

### Flat-to-Colon Mapping
The generator accepts both formats:
- `company_name` → mapped to `company:name`
- `company:name` → used directly

### Conditional Sections
```markdown
{{if:ecommerce}}
This content only appears for e-commerce sites.
{{endif}}

{{if:gdpr}}
GDPR-specific compliance text.
{{endif}}
```

Condition tokens checked against:
- Website type: `personal`, `ecommerce`, `social`
- Compliance/feature flags: `gdpr`, `ccpa`, `medical_content`, etc.

### Template Resolution
Priority order:
1. `legal/{websiteType}/{pageType}.md` (e.g., `legal/ecommerce/privacy-policy.md`)
2. `legal/base/{pageType}.md` (fallback)

## AJAX API

**Endpoint:** `ajax/ajax_legal_handler.php` (POST FormData)

### Actions
- `init` - Load server config defaults
- `get_page_types` - List available legal page types
- `get_website_presets` - Get defaults for website type
- `preview` - Generate HTML preview
- `generate` - Generate and save final output

### Response Format
```json
{
  "success": true|false,
  "html": "...",       // HTML output
  "markdown": "...",   // Markdown source
  "filename": "...",
  "message": "..."
}
```

### New AJAX Fields (as of latest)
- `output_format`: `html|markdown|both` (controls what server returns/saves)
- `theme`: `light|dark|auto` (UI theme preference)

## Config Integration

Uses `Yohns\Core\Config` to pull site defaults:
- `config/site.php` - Company name, URL, contact info
- `config/legal.php` - Legal-specific settings

Config keys auto-populate placeholders:
```php
'company_name' (site) → company:name
'site_url' (site) → website:url
```

## Important Constraints

1. **Do NOT rename placeholders** in templates without updating `LegalPageGenerator::setPlaceholders()` mapping logic
2. **Keep file writes** confined to designated output directory (currently `generated/legal/`)
3. **Preserve FormData keys** in AJAX - frontend expects specific response shape
4. **Theme detection** - Controller attempts client hints, falls back to `dark` if `theme` not provided

## Testing Minimal Changes

To validate template or generator changes:
1. Edit a template under `legal/base/` (e.g., add `{{company:name}}`)
2. Create quick test script:
```php
<?php
require 'vendor/autoload.php';
use Y0hn\Gens\Legal\LegalPageGenerator;

$gen = new LegalPageGenerator('privacy-policy', 'personal', [
    'company:name' => 'Test Co'
]);
echo $gen->generate();
```
3. Or run existing PHPUnit tests: `vendor/bin/phpunit tests/`

## Key Files for Reference

- `legal/implementation-guide.md` - Original class design specs
- `legal/placeholders/cheat-sheet.md` - Complete placeholder reference
- `ajax/ajax_legal_handler.php` - AJAX routing logic
- `Y0hn/Gens/Legal/LegalPageGenerator.php` - Core template engine (lines 1-300 contain key methods)

## Legacy Note

`legal-page-generator.html` is an older template kept for reference. Active UI is `legal-generator.html`.

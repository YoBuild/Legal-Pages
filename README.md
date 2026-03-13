# yohns/legal-pages

Generate customizable legal pages (Privacy Policy, Terms of Service, etc.) from Markdown templates with placeholder substitution — outputs Markdown or Bootstrap 5.3.8 HTML.

## Getting Started

**1. Install via Composer:**

```bash
composer require yohns/legal-pages
```

**2. Publish starter files:**

```bash
php vendor/bin/legal-pages publish
```

This copies a config file, a quickstart script, and an optional web UI into a `legal-pages/` directory in your project root.

**3. Edit your config and generate:**

```bash
# Edit legal-pages/config/legal-pages.php with your company details, then:
php legal-pages/generate.php
```

Output files (Markdown + Bootstrap HTML) are saved to `legal-pages/generated/`.

### Publish Options

```bash
# Publish everything (config + quickstart + web UI)
php vendor/bin/legal-pages publish

# Publish only specific parts
php vendor/bin/legal-pages publish --config
php vendor/bin/legal-pages publish --quickstart
php vendor/bin/legal-pages publish --ui

# Custom output directory
php vendor/bin/legal-pages publish --dir=my-legal/
```

## Programmatic Usage

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

// Get Bootstrap 5.3.8 HTML fragment (for embedding in your page)
$fragment = $gen->convertToHtml();

// Get full standalone HTML page with Bootstrap CDN
$fullPage = $gen->convertToHtml(full: true);

// Save to file
$gen->savePage($fullPage, 'privacy-policy.html', __DIR__ . '/generated');
$gen->savePage($markdown, 'privacy-policy.md', __DIR__ . '/generated');
```

## Output Formats

### Markdown (`generate()`)

Returns raw Markdown with all placeholders replaced and conditional sections processed.

```php
$markdown = $gen->generate();
file_put_contents('privacy-policy.md', $markdown);
```

### Bootstrap HTML Fragment (`convertToHtml()`)

Returns HTML with Bootstrap 5.3.8 CSS classes on elements. No `<html>`, `<head>`, or `<body>` tags — just the content ready to embed in your existing page.

```php
$html = $gen->convertToHtml();
// Output example:
// <h1 class="mb-3">Privacy Policy</h1>
// <p class="mb-2">Welcome to Acme Inc...</p>
```

Bootstrap classes applied:

| Element          | Classes                              |
|------------------|--------------------------------------|
| `<h1>` - `<h6>` | `mb-3`                               |
| `<p>`            | `mb-2`                               |
| `<table>`        | `table table-striped`                |
| `<ul>`, `<ol>`   | `ps-3`                               |
| `<blockquote>`   | `blockquote ps-3 border-start border-4` |
| `<a>`            | `link-primary`                       |
| `<hr>`           | `my-4`                               |

### Full HTML Page (`convertToHtml(full: true)`)

Returns a complete HTML document with the Bootstrap 5.3.8 CDN stylesheet included. Ready to save and open in a browser.

```php
$fullPage = $gen->convertToHtml(full: true);
file_put_contents('privacy-policy.html', $fullPage);
```

The page title is derived from the page type (e.g., `privacy-policy` becomes `Privacy Policy`).

### Saving Files (`savePage()`)

Save generated content to any directory. The directory is created automatically if it doesn't exist.

```php
$gen->savePage($content, 'filename.html', '/path/to/output/');
```

Parameters:
- `$content` — The generated content (markdown or HTML)
- `$filename` — Output filename (sanitized with `basename()`)
- `$outputDir` — Directory to save to (created if missing)

## Available Templates

### Base Templates (all website types)

| Page Type               | Template File                          |
|-------------------------|----------------------------------------|
| `privacy-policy`        | `legal/base/privacy-policy.md`         |
| `terms-of-service`      | `legal/base/terms-of-service.md`       |
| `cookie-policy`         | `legal/base/cookie-policy.md`          |
| `dmca-policy`           | `legal/base/dmca-policy.md`            |
| `accessibility-statement` | `legal/base/accessibility-statement.md` |

### Website-Specific Templates

| Website Type | Template                               |
|-------------|----------------------------------------|
| `personal`  | `legal/personal/blog-disclaimer.md`    |
| `ecommerce` | `legal/ecommerce/refund-policy.md`     |
| `ecommerce` | `legal/ecommerce/shipping-policy.md`   |
| `social`    | `legal/social/content-policy.md`       |

Template resolution order:
1. `legal/{websiteType}/{pageType}.md` (website-specific)
2. `legal/base/{pageType}.md` (fallback)

### List Available Templates

```php
$gen = new LegalPageGenerator();
$templates = $gen->getAvailableTemplates();
// ['Accessibility Statement', 'Blog Disclaimer', 'Cookie Policy', ...]
```

## Website Types

Pass the website type as the second constructor parameter:

```php
// Personal blog
$gen = new LegalPageGenerator('privacy-policy', 'personal', $config);

// Online store
$gen = new LegalPageGenerator('privacy-policy', 'ecommerce', $config);

// Social network
$gen = new LegalPageGenerator('privacy-policy', 'social', $config);
```

The website type controls:
- Which templates are available (e.g., `refund-policy` only for ecommerce)
- Which conditional sections appear (`{{if:ecommerce}}...{{endif}}`)

## Placeholders

Templates use `{{category:field}}` syntax. Pass values via the constructor or setter methods.

### Via Constructor (nested array)

```php
$gen = new LegalPageGenerator('privacy-policy', 'ecommerce', [
    'company' => [
        'name'  => 'Acme Inc',
        'email' => 'legal@acme.com',
        'phone' => '+1-555-0100',
    ],
    'website' => [
        'url'  => 'https://acme.com',
        'name' => 'Acme Store',
    ],
    'data' => [
        'collected' => 'name, email, payment information',
        'retention' => '24 months',
    ],
]);
```

### Via Constructor (flat format)

```php
$gen = new LegalPageGenerator('privacy-policy', 'ecommerce', [
    'company:name'  => 'Acme Inc',
    'company:email' => 'legal@acme.com',
    'website:url'   => 'https://acme.com',
]);
```

### Via Setter Methods

```php
$gen->setPlaceholder('company', 'name', 'Acme Inc');
// or
$gen->setPlaceholders([
    'company:name'  => 'Acme Inc',
    'company_name'  => 'Acme Inc',  // underscore format also works
]);
```

### Auto-Populated Placeholders

When `yohns/config` is configured with `config/config.php`, these placeholders are filled automatically:

| Placeholder        | Config Key        |
|--------------------|-------------------|
| `company:name`     | `company_name`    |
| `company:address`  | `company_address` |
| `company:email`    | `contact_email`   |
| `company:phone`    | `contact_phone`   |
| `company:country`  | `company_country` |
| `website:url`      | `site_url`        |
| `website:name`     | `site_name`       |
| `current:date`     | Auto (today)      |
| `current:year`     | Auto (this year)  |

### Common Placeholder Categories

| Category     | Fields                                                    |
|-------------|-----------------------------------------------------------|
| `company`   | `name`, `legal_name`, `email`, `phone`, `address`, `city`, `state`, `zip`, `country` |
| `website`   | `url`, `name`, `domain`                                   |
| `data`      | `collected`, `cookies`, `retention`, `sharing`, `location` |
| `compliance`| `gdpr`, `ccpa`, `coppa`, `ada`, `dpa_email`               |
| `feature`   | `newsletter`, `analytics`, `medical_content`, `legal_content` |
| `ecommerce` | `payment_processors`, `shipping_providers`, `return_period`, `shipping_time`, `refund_time` |
| `social`    | `content_policy`, `minimum_age`, `reporting`, `account_termination`, `content_rights` |

See `legal/placeholders/cheat-sheet.md` for the complete reference.

## Conditional Sections

Templates support conditional blocks based on website type or feature/compliance flags:

```markdown
{{if:ecommerce}}
This section only appears for e-commerce sites.
{{endif}}

{{if:gdpr}}
GDPR-specific compliance text.
{{endif}}
```

Enable compliance flags via placeholders:

```php
$gen->setPlaceholders([
    'compliance:gdpr' => 'true',
    'compliance:ccpa' => 'true',
]);
```

## Custom Template Directory

By default, templates are loaded from `legal/` relative to the package root. Pass a custom path as the 4th constructor parameter:

```php
$gen = new LegalPageGenerator(
    'privacy-policy',
    'ecommerce',
    $config,
    '/path/to/my/templates'
);
```

Your custom directory should follow the same structure:

```
my-templates/
  base/
    privacy-policy.md
    terms-of-service.md
  ecommerce/
    refund-policy.md
```

## Config Integration

This package uses `yohns/config` to auto-load site defaults. Set up your config files:

**config/config.php** — Core site/company values (loaded first, available to other configs):
```php
return [
    'company_name'    => 'Your Company, Inc.',
    'site_url'        => 'https://yoursite.com',
    'site_name'       => 'Your Site',
    'contact_email'   => 'contact@yoursite.com',
    'company_address' => '123 Main St',
    'contact_phone'   => '+1-555-0100',
    'company_country' => 'United States',
];
```

**config/legal-pages.php** — Legal-specific defaults, compliance flags, and page-specific placeholders:
```php
return [
    // Compliance flags
    'compliance:gdpr' => true,
    'compliance:ccpa' => false,

    // Feature flags
    'feature:analytics'  => true,
    'feature:newsletter' => false,

    // Privacy Policy placeholders
    'data:collected' => 'name, email address, IP address',
    'data:cookies'   => 'essential, analytics',
    'data:retention' => '24 months',

    // Ecommerce placeholders
    'ecommerce:return_period' => '30 days',
    'ecommerce:refund_time'   => '7-10 business days',
    // ... see config/legal-pages.php for complete reference
];
```

These values are automatically used as placeholder defaults. Values passed to the constructor override config values.

## Full Example: Generate All Legal Pages

```php
<?php
require 'vendor/autoload.php';

use Yohns\Gens\Legal\LegalPageGenerator;

$outputDir = __DIR__ . '/generated/legal';
$websiteType = 'ecommerce';

$config = [
    'company' => ['name' => 'Acme Inc', 'email' => 'legal@acme.com'],
    'website' => ['url' => 'https://acme.com', 'name' => 'Acme Store'],
    'compliance' => ['gdpr' => 'true'],
];

$pages = [
    'privacy-policy',
    'terms-of-service',
    'cookie-policy',
    'refund-policy',
    'shipping-policy',
];

foreach ($pages as $pageType) {
    $gen = new LegalPageGenerator($pageType, $websiteType, $config);

    // Save as Markdown
    $gen->savePage($gen->generate(), "$pageType.md", $outputDir);

    // Save as full HTML page
    $gen->savePage($gen->convertToHtml(full: true), "$pageType.html", $outputDir);

    echo "Generated: $pageType\n";
}
```

## Testing

```bash
composer install
vendor/bin/phpunit tests/
```

## License

MIT

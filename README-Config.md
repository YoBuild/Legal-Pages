# Configuration Guide for Legal Page Generator

This document explains how the Legal Page Generator uses configuration files to populate form fields and template placeholders.

## Overview

The generator reads from two configuration files to automatically populate default values:

1. **`config/site.php`** - General company and website information
2. **`config/legal.php`** - Legal-specific settings and compliance flags

Values from these files are automatically loaded when you initialize the form and used as defaults for generating legal documents.

## Configuration Files

### 1. config/site.php

Contains general site and company information that applies across your entire application.

```php
<?php
return [
    // Company information
    'company_name'        => 'Your Company, Inc.',
    'company_legal_name'  => 'Your Company, Inc.',
    'company_address'     => '123 Main Street',
    'company_city'        => 'Anytown',
    'company_state'       => 'State',
    'company_zip'         => '12345',
    'company_country'     => 'United States',
    'company_phone'       => '+1-555-0100',
    'contact_email'       => 'contact@example.com',

    // Website information
    'site_url'            => 'http://localhost:8000',
    'site_name'           => 'Example Site',
];
```

### 2. config/legal.php

Contains legal document-specific settings, compliance flags, and feature toggles.

```php
<?php
return [
    // Override company placeholders (optional)
    'company:name'    => 'Your Company, Inc.',
    'company:email'   => 'contact@example.com',
    'company:phone'   => '+1-555-0100',
    'company:country' => 'United States',

    // Compliance flags
    'compliance:gdpr' => false,
    'compliance:ccpa' => false,
    'compliance:coppa' => false,
    'compliance:ada'  => false,

    // Feature flags
    'feature:newsletter' => false,
    'feature:analytics'  => true,
];
```

## Config Key to Placeholder Mapping

The system converts configuration keys to template placeholders using colon-format notation.

### Company Information

| Config Key (site.php) | Placeholder | Example Value | Used In |
|----------------------|-------------|---------------|---------|
| `company_name` | `{{company:name}}` | Acme Corporation | All templates |
| `company_legal_name` | `{{company:legal_name}}` | Acme Corporation LLC | Terms of Service |
| `company_address` | `{{company:address}}` | 123 Main Street | All templates |
| `company_city` | `{{company:city}}` | San Francisco | Contact sections |
| `company_state` | `{{company:state}}` | California | Contact sections |
| `company_zip` | `{{company:zip}}` | 94102 | Contact sections |
| `company_country` | `{{company:country}}` | United States | All templates |
| `company_phone` | `{{company:phone}}` | +1-555-0100 | Contact sections |
| `contact_email` | `{{company:email}}` | legal@example.com | All templates |

### Website Information

| Config Key (site.php) | Placeholder | Example Value | Used In |
|----------------------|-------------|---------------|---------|
| `site_url` | `{{website:url}}` | https://example.com | All templates |
| `site_name` | `{{website:name}}` | My Awesome Site | All templates |

### Compliance Flags

| Config Key (legal.php) | Placeholder | Values | Effect |
|-----------------------|-------------|--------|--------|
| `compliance:gdpr` | `{{if:gdpr}}...{{endif}}` | true/false | Shows/hides GDPR sections |
| `compliance:ccpa` | `{{if:ccpa}}...{{endif}}` | true/false | Shows/hides CCPA sections |
| `compliance:coppa` | `{{if:coppa}}...{{endif}}` | true/false | Shows/hides COPPA sections |
| `compliance:ada` | N/A | true/false | Enables accessibility features |

### Feature Flags

| Config Key (legal.php) | Effect |
|-----------------------|--------|
| `feature:newsletter` | Shows newsletter-related data collection notices |
| `feature:analytics` | Shows analytics tracking notices |

### Auto-Generated Values

These values are generated automatically and cannot be configured:

| Placeholder | Generated Value | Example |
|-------------|----------------|---------|
| `{{current:date}}` | Current date (long format) | November 11, 2025 |
| `{{current:year}}` | Current year | 2025 |

## How Configuration Flows Through the System

```
┌─────────────────────┐
│  config/site.php    │  ← General site information
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  config/legal.php   │  ← Legal-specific overrides & flags
└──────────┬──────────┘
           │
           ▼
┌────────────────────────────────────────────┐
│  LegalPageConfig::getDefaultPlaceholders() │
│  Merges configs + auto-generated values    │
└──────────┬─────────────────────────────────┘
           │
           ▼
┌─────────────────────┐
│  AJAX: init action  │  ← Returns JSON with all defaults
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  Frontend Form      │  ← Pre-fills form fields
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  User Customization │  ← User can override any value
└──────────┬──────────┘
           │
           ▼
┌───────────────────────┐
│  Template Generation  │  ← Placeholders replaced with values
└───────────────────────┘
```

## Form Field Pre-Population

When the form loads (AJAX `init` action), these fields are automatically populated:

### Step 3: Customization Form

**Company Information Section:**
- Company Name → `company:name`
- Company Email → `company:email`
- Company Phone → `company:phone`
- Company Address → `company:address`
- Country → `company:country`

**Website Information Section:**
- Website URL → `website:url`
- Website Name → `website:name`

**Compliance Checkboxes:**
- GDPR Compliance → `compliance:gdpr`
- CCPA Compliance → `compliance:ccpa`
- COPPA Compliance → `compliance:coppa`

**Data Collection Fields:**
These are populated based on website type (personal/ecommerce/social) but can reference config values:
- Data Collected → `data:collected`
- Cookie Types → `data:cookies`
- Data Retention Period → `data:retention`
- Third-Party Sharing → `data:sharing`

## Customization Examples

### Example 1: Basic Company Setup

Edit `config/site.php`:

```php
'company_name'   => 'Acme Corporation',
'contact_email'  => 'legal@acme.com',
'company_phone'  => '+1-800-ACME-NOW',
'company_address'=> '456 Tech Boulevard',
'company_country'=> 'United States',
'site_url'       => 'https://acme.com',
'site_name'      => 'Acme Corp',
```

Result: All legal documents will automatically use "Acme Corporation" and contact details.

### Example 2: Enable GDPR Compliance

Edit `config/legal.php`:

```php
'compliance:gdpr' => true,
'compliance:dpa_email' => 'dpo@acme.com',
```

Result: GDPR sections will appear in Privacy Policy with DPA contact information.

### Example 3: E-commerce Site with California Customers

Edit `config/legal.php`:

```php
'compliance:ccpa' => true,
'company:country' => 'United States',
```

Result: CCPA compliance sections will be included in legal documents.

### Example 4: Override Site Config in Legal Config

If you want different contact info for legal matters:

```php
// config/legal.php
'company:email' => 'legal@example.com',  // Overrides contact_email from site.php
'company:phone' => '+1-800-LEGAL-00',    // Overrides company_phone from site.php
```

Result: Legal documents will use `legal@example.com` instead of the general contact email.

## Website Type Presets

In addition to config files, the generator provides **hardcoded presets** based on website type:

### Personal Blog/Website
- Minimal data collection
- Basic cookies (essential, analytics)
- 12-month retention
- No compliance requirements by default

### E-commerce
- Extended data collection (billing, shipping, payment)
- Multiple cookie types (essential, analytics, marketing)
- 36-month retention
- CCPA enabled by default

### Social Network
- Extensive data collection (profiles, content, connections)
- All cookie types
- 48-month retention
- GDPR, CCPA, and COPPA enabled by default

**Note:** Config file values take precedence over website type presets.

## Priority Order

When the same placeholder exists in multiple places, values are applied in this order (last wins):

1. **Auto-generated values** (current:date, current:year)
2. **config/site.php** (general site information)
3. **config/legal.php** (legal-specific overrides)
4. **Website type presets** (hardcoded defaults in JavaScript)
5. **User form input** (highest priority - user can override anything)

## Code References

- **Config Loading:** `Y0hn/Gens/Legal/LegalPageConfig.php:55-101`
- **Placeholder Merging:** `Y0hn/Gens/Legal/LegalPageConfig.php:110-112`
- **AJAX Init Handler:** `Y0hn/Gens/Legal/LegalPageController.php:110-126`
- **Frontend Preset Loading:** `js/legal-generator.js:468-518`

## Testing Configuration

To verify your configuration is working:

1. Start the development server:
   ```bash
   php -S localhost:8000 -t .
   ```

2. Visit: http://localhost:8000/legal-generator.html

3. Open browser console and run:
   ```javascript
   getServerDefaults().then(data => console.log(data))
   ```

4. Check the console output - it should show your configured values.

## Troubleshooting

**Issue:** Form fields are empty
- **Solution:** Check that `config/site.php` and `config/legal.php` exist and are readable
- **Solution:** Verify Composer autoloader is loaded (`vendor/autoload.php`)

**Issue:** Config values not updating
- **Solution:** Clear browser cache and sessionStorage
- **Solution:** Restart PHP development server

**Issue:** Some placeholders remain unreplaced (e.g., `{{data:cookies}}`)
- **Solution:** These are user-input fields that must be filled in Step 3
- **Solution:** Add them to `config/legal.php` to provide defaults

## Adding Custom Config Values

You can add any custom placeholder to `config/legal.php`:

```php
// config/legal.php
return [
    // ... existing config ...

    // Custom placeholders
    'data:collected' => 'name, email, IP address, browser info',
    'data:cookies' => 'essential, analytics, marketing',
    'data:retention' => '24 months',
    'ecommerce:return_period' => '30 days',
    'social:minimum_age' => '13',
];
```

These will automatically populate form fields and be available as template placeholders.

## Related Documentation

- **CLAUDE.md** - Development guide for working with this codebase
- **legal/placeholders/cheat-sheet.md** - Complete placeholder reference
- **legal/implementation-guide.md** - Technical implementation details

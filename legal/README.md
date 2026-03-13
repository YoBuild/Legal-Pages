# Legal Templates

Markdown templates for generating legal pages. Each template uses `{{category:field}}` placeholders and `{{if:token}}...{{endif}}` conditional blocks that are processed by `LegalPageGenerator`.

## Directory Structure

### `base/` — Universal Templates

Available for all website types. These are the fallback templates when no website-specific version exists.

| File | Description |
|------|-------------|
| [privacy-policy.md](base/privacy-policy.md) | How the site collects, uses, and protects user data |
| [terms-of-service.md](base/terms-of-service.md) | Rules and conditions for using the website |
| [cookie-policy.md](base/cookie-policy.md) | What cookies are used and how users can manage them |
| [dmca-policy.md](base/dmca-policy.md) | Copyright infringement notice and takedown procedures |
| [accessibility-statement.md](base/accessibility-statement.md) | Commitment to web accessibility standards (WCAG) |

### `personal/` — Personal Blog Templates

| File | Description |
|------|-------------|
| [blog-disclaimer.md](personal/blog-disclaimer.md) | Disclaimers for blog content (opinions, advice, affiliates). Supports conditional sections for medical, legal, and financial content disclaimers via feature flags. |

### `ecommerce/` — E-Commerce Templates

| File | Description |
|------|-------------|
| [refund-policy.md](ecommerce/refund-policy.md) | Return windows, refund processing times, and conditions |
| [shipping-policy.md](ecommerce/shipping-policy.md) | Shipping carriers, delivery timeframes, and coverage areas |

### `social/` — Social Network Templates

| File | Description |
|------|-------------|
| [content-policy.md](social/content-policy.md) | Prohibited content, reporting mechanisms, and account termination rules |

### `placeholders/` — Reference Documentation

| File | Description |
|------|-------------|
| [cheat-sheet.md](placeholders/cheat-sheet.md) | Quick reference for all available placeholders by category |

## Template Resolution

When generating a page, the system checks for templates in this order:

1. `legal/{websiteType}/{pageType}.md` (e.g., `legal/ecommerce/refund-policy.md`)
2. `legal/base/{pageType}.md` (fallback)

## Adding New Templates

1. Create a `.md` file in the appropriate subdirectory
2. Use `{{category:field}}` placeholders for dynamic values
3. Use `{{if:token}}...{{endif}}` for conditional sections
4. Add the placeholder keys to `config/legal-pages.php`

See `placeholders/cheat-sheet.md` for the full list of available placeholders.

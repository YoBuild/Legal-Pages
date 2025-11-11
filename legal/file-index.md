# Legal Page Generator - File Index

## Overview
This system provides customizable legal page templates for different types of websites. The templates use a placeholder system to easily personalize content while maintaining legally appropriate language for personal blogs, e-commerce stores, and social networking sites.

## Documentation Files

| File | Purpose |
|------|---------|
| `/legal/README.md` | Main documentation explaining the system structure and usage |
| `/legal/implementation-guide.md` | Detailed implementation guide for the PHP classes |
| `/legal/placeholders/README.md` | Documentation of all available placeholders |
| `/legal/placeholders/cheat-sheet.md` | Quick reference for placeholders by template type |

## Base Templates

These templates are available for all website types:

| File | Description |
|------|-------------|
| `/legal/base/privacy-policy.md` | Explains how you collect, use, and share user data |
| `/legal/base/terms-of-service.md` | Sets the rules users agree to when using your site |
| `/legal/base/cookie-policy.md` | Details the cookies your site uses and their purposes |
| `/legal/base/dmca-policy.md` | Details how you handle copyright infringement claims |
| `/legal/base/accessibility-statement.md` | Explains your commitment to digital accessibility |

## Website-Specific Templates

### Personal Blog/Website

| File | Description |
|------|-------------|
| `/legal/personal/blog-disclaimer.md` | Clarifies limitations of blog content and your liability |

### E-commerce (USA)

| File | Description |
|------|-------------|
| `/legal/ecommerce/refund-policy.md` | Outlines your return and refund processes |
| `/legal/ecommerce/shipping-policy.md` | Explains your shipping methods, timeframes, and costs |

### Social Network

| File | Description |
|------|-------------|
| `/legal/social/content-policy.md` | Sets guidelines for user-generated content |

## Implementation Structure

The PHP implementation will use the `Y0hn\Gens\FooterPages` namespace with these classes:

| Class | Purpose |
|-------|---------|
| `LegalPageForm` | Handles the multi-step form for creating legal pages |
| `LegalPageGenerator` | Generates legal pages from templates with placeholder replacement |
| `LegalPageTemplate` | Manages the loading and information about templates |
| `LegalPageConfig` | Reads default values from configuration |
| `LegalContentPresets` | Provides default content for different website types |
| `LegalPageController` | Handles AJAX requests for the form |

## Planned Additional Templates

These templates could be added in future updates:

1. EULA (End User License Agreement)
2. Acceptable Use Policy
3. Copyright Notice
4. GDPR Compliance Statement
5. CCPA Compliance Statement
6. Community Guidelines (Social)
7. Affiliate Disclosure (Personal/E-commerce)
8. Warranty Policy (E-commerce)
9. User Account Policy (Social/E-commerce)
10. Data Breach Response Policy

## Next Steps

1. Implement the PHP classes as outlined in the implementation guide
2. Create the frontend form with Bootstrap 5 and vanilla JavaScript
3. Add AJAX handling for form submissions and previews
4. Implement Markdown to HTML conversion
5. Add template versioning and update tracking
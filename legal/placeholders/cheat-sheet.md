# Legal Page Generator - Placeholder Cheat Sheet

This document provides a quick reference for all placeholders needed for each type of legal page, organized by website type.

## Common Placeholders (All Website Types)

These placeholders should be filled for all legal pages regardless of website type:

| Placeholder | Description | Example |
|-------------|-------------|---------|
| `{{company:name}}` | The name of your company/organization | Acme Inc. |
| `{{company:legal_name}}` | Legal registered name (if different) | Acme Incorporated LLC |
| `{{company:address}}` | Physical address | 123 Main St, Anytown, CA 94001 |
| `{{company:country}}` | Country of business registration | United States |
| `{{company:email}}` | Contact email address | support@example.com |
| `{{company:phone}}` | Contact phone number | (555) 123-4567 |
| `{{website:name}}` | Website name | My Awesome Blog |
| `{{website:url}}` | Full URL | https://example.com |
| `{{website:domain}}` | Just the domain | example.com |
| `{{current:date}}` | Current date (auto-generated) | November 10, 2025 |

## Privacy Policy Placeholders

### All Website Types
| Placeholder | Description | Example |
|-------------|-------------|---------|
| `{{data:collected}}` | List of data types collected | name, email, IP address |
| `{{data:cookies}}` | Types of cookies used | essential, analytics, marketing |
| `{{data:retention}}` | How long data is kept | 24 months |
| `{{data:sharing}}` | Third parties data is shared with | payment processors, analytics providers |
| `{{data:location}}` | Where data is stored | United States, EU |

### GDPR Compliance (Optional)
| Placeholder | Description | Example |
|-------------|-------------|---------|
| `{{compliance:gdpr}}` | GDPR compliance status | true/false |
| `{{compliance:dpa_email}}` | Data Protection Officer email | dpo@example.com |

### CCPA Compliance (Optional)
| Placeholder | Description | Example |
|-------------|-------------|---------|
| `{{compliance:ccpa}}` | CCPA compliance status | true/false |

## Terms of Service Placeholders

### E-commerce Specific
| Placeholder | Description | Example |
|-------------|-------------|---------|
| `{{ecommerce:payment_processors}}` | Payment methods accepted | Stripe, PayPal, credit cards |
| `{{ecommerce:shipping_providers}}` | Shipping carriers used | USPS, FedEx, UPS |
| `{{ecommerce:return_period}}` | Days allowed for returns | 30 days |
| `{{ecommerce:shipping_time}}` | Estimated shipping timeframe | 3-5 business days |
| `{{ecommerce:refund_time}}` | Days to process refunds | 14 business days |

### Social Network Specific
| Placeholder | Description | Example |
|-------------|-------------|---------|
| `{{social:minimum_age}}` | Minimum user age | 13 years |
| `{{social:account_termination}}` | Account termination policy | after 3 violations |
| `{{social:content_rights}}` | User content ownership | users retain ownership but grant license |

## Cookie Policy Placeholders

| Placeholder | Description | Example |
|-------------|-------------|---------|
| `{{data:cookies}}` | Types of cookies used | essential, analytics, marketing |

## Refund Policy Placeholders (E-commerce)

| Placeholder | Description | Example |
|-------------|-------------|---------|
| `{{ecommerce:return_period}}` | Days allowed for returns | 30 days |
| `{{ecommerce:refund_time}}` | Days to process refunds | 14 business days |

## Shipping Policy Placeholders (E-commerce)

| Placeholder | Description | Example |
|-------------|-------------|---------|
| `{{ecommerce:shipping_providers}}` | Shipping carriers used | USPS, FedEx, UPS |
| `{{ecommerce:shipping_time}}` | Estimated shipping timeframe | 3-5 business days |

## Content Policy Placeholders (Social Network)

| Placeholder | Description | Example |
|-------------|-------------|---------|
| `{{social:content_policy}}` | Content allowed/prohibited | no hate speech, violence, or illegal content |
| `{{social:minimum_age}}` | Minimum user age | 13 years |
| `{{social:reporting}}` | How to report violations | report button, contact@example.com |
| `{{social:account_termination}}` | Account termination policy | after 3 violations |

## Blog Disclaimer Placeholders (Personal)

| Placeholder | Description | Example |
|-------------|-------------|---------|
| `{{feature:medical_content}}` | Include medical disclaimer | true/false |
| `{{feature:legal_content}}` | Include legal disclaimer | true/false |
| `{{feature:financial_content}}` | Include financial disclaimer | true/false |

## Conditional Sections

These are used to include/exclude entire sections of content based on website type or features:

| Condition | Description | Appears In |
|-----------|-------------|------------|
| `{{if:personal}}` | Content specific to personal websites | Various templates |
| `{{if:ecommerce}}` | Content specific to online shops | Various templates |
| `{{if:social}}` | Content specific to social networks | Various templates |
| `{{if:gdpr}}` | GDPR compliance sections | Privacy Policy, Cookie Policy |
| `{{if:ccpa}}` | CCPA compliance sections | Privacy Policy |
| `{{if:medical_content}}` | Medical disclaimer | Blog Disclaimer |
| `{{if:legal_content}}` | Legal advice disclaimer | Blog Disclaimer |
| `{{if:financial_content}}` | Financial advice disclaimer | Blog Disclaimer |

## Website Type Quick Reference

### Personal Blog/Website
- Privacy Policy
- Terms of Service
- Cookie Policy
- Blog Disclaimer
- DMCA Policy (if accepting user comments)
- Accessibility Statement

### E-commerce (USA)
- Privacy Policy
- Terms of Service
- Cookie Policy
- Refund Policy
- Shipping Policy
- DMCA Policy
- Accessibility Statement

### Social Network
- Privacy Policy
- Terms of Service
- Cookie Policy
- Content Policy
- DMCA Policy
- Accessibility Statement

## Implementation Example

1. Select website type (e.g., E-commerce)
2. Select legal page type (e.g., Privacy Policy)
3. Fill in the common placeholders:
   ```
   company:name = "My Online Store"
   company:address = "123 Main St, Anytown, CA 94001"
   website:url = "https://mystore.example.com"
   ```
4. Fill in the page-specific placeholders:
   ```
   data:collected = "name, email, shipping address, billing address"
   data:retention = "36 months"
   compliance:ccpa = "true"
   ```
5. Generate the final document with all placeholders replaced
# Legal Page Generator Placeholders

This document lists all available placeholders that can be used in the legal page templates.

## Basic Information

| Placeholder | Description | Example |
|-------------|-------------|---------|
| `{{company:name}}` | The name of your company/organization | Acme Inc. |
| `{{company:legal_name}}` | Legal registered name (if different) | Acme Incorporated LLC |
| `{{company:address}}` | Physical address | 123 Main St, Anytown, CA 94001 |
| `{{company:country}}` | Country of business registration | United States |
| `{{company:email}}` | Contact email address | support@example.com |
| `{{company:phone}}` | Contact phone number | (555) 123-4567 |
| `{{company:registration}}` | Business registration number | EIN: 12-3456789 |

## Website Details

| Placeholder | Description | Example |
|-------------|-------------|---------|
| `{{website:name}}` | Website name | My Awesome Blog |
| `{{website:url}}` | Full URL | https://example.com |
| `{{website:domain}}` | Just the domain | example.com |
| `{{website:description}}` | Brief description | A blog about technology and science |
| `{{website:founding_date}}` | When the site was established | January 1, 2023 |

## User Account Data

| Placeholder | Description | Example |
|-------------|-------------|---------|
| `{{data:collected}}` | List of data types collected | name, email, IP address |
| `{{data:cookies}}` | Types of cookies used | essential, analytics, marketing |
| `{{data:retention}}` | How long data is kept | 24 months |
| `{{data:sharing}}` | Third parties data is shared with | payment processors, analytics providers |
| `{{data:location}}` | Where data is stored | United States, EU |

## E-commerce Specific

| Placeholder | Description | Example |
|-------------|-------------|---------|
| `{{ecommerce:payment_processors}}` | Payment methods accepted | Stripe, PayPal, credit cards |
| `{{ecommerce:shipping_providers}}` | Shipping carriers used | USPS, FedEx, UPS |
| `{{ecommerce:return_period}}` | Days allowed for returns | 30 days |
| `{{ecommerce:shipping_time}}` | Estimated shipping timeframe | 3-5 business days |
| `{{ecommerce:refund_time}}` | Days to process refunds | 14 business days |

## Social Network Specific

| Placeholder | Description | Example |
|-------------|-------------|---------|
| `{{social:content_policy}}` | Content allowed/prohibited | no hate speech, violence, or illegal content |
| `{{social:minimum_age}}` | Minimum user age | 13 years |
| `{{social:reporting}}` | How to report violations | report button, contact@example.com |
| `{{social:account_termination}}` | Account termination policy | after 3 violations |
| `{{social:content_rights}}` | User content ownership | users retain ownership but grant license |

## Compliance Settings

| Placeholder | Description | Example |
|-------------|-------------|---------|
| `{{compliance:gdpr}}` | GDPR compliance status | true/false |
| `{{compliance:ccpa}}` | CCPA compliance status | true/false |
| `{{compliance:coppa}}` | COPPA compliance status | true/false |
| `{{compliance:ada}}` | ADA compliance status | true/false |
| `{{compliance:dpa_email}}` | Data Protection Officer email | dpo@example.com |

## Conditional Sections

These are used for including/excluding entire sections:

| Condition | Description | Example |
|-----------|-------------|---------|
| `{{if:personal}}` | Include for personal websites | Personal blog disclaimer |
| `{{if:ecommerce}}` | Include for online shops | Return policy details |
| `{{if:social}}` | Include for social networks | User-generated content rules |
| `{{if:gdpr}}` | Include for GDPR compliance | EU user rights |
| `{{if:ccpa}}` | Include for CCPA compliance | California privacy rights |
| `{{if:coppa}}` | Include for COPPA compliance | Parental consent details |

## Custom Fields

You can also define custom placeholders for special needs:
- `{{custom:field_name}}`

Remember to replace all placeholders before publishing your legal documents, and have them reviewed by a qualified attorney.
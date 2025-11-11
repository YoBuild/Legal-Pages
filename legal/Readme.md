# Legal Page Generator

This system provides customizable legal page templates for different types of websites. The templates use a placeholder system to easily personalize content while maintaining legally appropriate language.

## Directory Structure

```
/legal/
  /base/           # Common sections shared across templates
  /personal/       # Templates for personal blogs
  /ecommerce/      # Templates for online shops (USA)
  /social/         # Templates for social networks
  /placeholders/   # Documentation of all available placeholders
```

## Placeholder System

Templates use two types of placeholders:

1. **Basic Placeholders**: `{{category:field}}` format
   - Example: `{{company:name}}` will be replaced with the company name

2. **Conditional Sections**:
   ```
   {{if:condition}}
   Content only included when condition is true
   {{endif}}
   ```
   - Conditions can be website types: `{{if:ecommerce}}` or compliance requirements: `{{if:gdpr}}`

## Usage with Y0hn\Gens\FooterPages

Implement a PHP class to process these markdown templates:

1. Create a `LegalPageGenerator` class that:
   - Takes website type and config data as input
   - Loads templates based on website type
   - Replaces placeholders with provided values
   - Processes conditional sections based on website type and settings

2. Create a form interface that:
   - Allows users to select legal page type
   - Choose website template type
   - Customize fields with form inputs
   - Preview and generate final documents

## Integration

1. The system should read from your website config to pre-fill common values
2. Generated markdown can be converted to HTML for web display
3. Advanced implementations can version legal documents and track changes

## Compliance Support

Templates include sections for major regulations:
- GDPR (General Data Protection Regulation) for EU users
- CCPA (California Consumer Privacy Act) for California residents
- ADA compliance statements
- General best practices for legal disclosures

## Customization

Each template is designed to be legally sound for its website category while allowing for customization of specific business details. Review all legal text with a qualified attorney before implementing on a production website.
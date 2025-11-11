# Legal Page Generator System Overview

I've created a comprehensive system for generating legal pages for different types of websites. The system includes markdown templates with placeholders that can be customized based on the website type and specific needs.

## Files Created

1. **Main documentation and structure files:**
   - `/legal/README.md` - Main documentation
   - `/legal/implementation-guide.md` - Detailed PHP implementation guide
   - `/legal/file-index.md` - Summary of all files
   - `/legal/placeholders/README.md` - Documentation of all placeholders
   - `/legal/placeholders/cheat-sheet.md` - Quick reference for placeholders

2. **Base templates (for all website types):**
   - `/legal/base/privacy-policy.md`
   - `/legal/base/terms-of-service.md`
   - `/legal/base/cookie-policy.md`
   - `/legal/base/dmca-policy.md`
   - `/legal/base/accessibility-statement.md`

3. **Website-specific templates:**
   - `/legal/personal/blog-disclaimer.md`
   - `/legal/ecommerce/refund-policy.md`
   - `/legal/ecommerce/shipping-policy.md`
   - `/legal/social/content-policy.md`

## How the System Works

1. **Placeholder System:**
   - Templates use `{{category:field}}` format (e.g., `{{company:name}}`)
   - Conditional sections use `{{if:condition}}...{{endif}}` format

2. **Website Types:**
   - Personal blogging and information
   - Online shop for USA distribution
   - Social networking website

3. **Workflow:**
   - Select legal page type (Terms of Service, Privacy Policy, etc.)
   - Choose website type (Personal, E-commerce, Social)
   - System auto-fills relevant content based on website type
   - Customize specific fields as needed
   - Generate final document

## Implementation Instructions

To implement this system in your PHP application:

1. Use the `Y0hn\Gens\FooterPages` namespace as specified
2. Create the core classes outlined in the implementation guide
3. Build a multi-step form with Bootstrap 5 and vanilla JavaScript
4. Process AJAX requests through `./index.php` with `domain.tld/ajax/` URLs
5. Return all responses in JSON format
6. Ensure security with input validation and sanitization

## Key Features of the System

1. **Intelligent Content Presets:**
   - Different content defaults based on website type
   - Legal language appropriate for each business model
   - Compliance sections for GDPR, CCPA, etc.

2. **Customizable Templates:**
   - All templates have appropriate placeholders for customization
   - Website-specific templates for specialized needs
   - Conditional sections that appear only when needed

3. **Compliance Built-in:**
   - GDPR sections for EU users
   - CCPA sections for California residents
   - ADA compliance statements
   - Industry-standard legal language

4. **Flexible Architecture:**
   - Base templates that work for all website types
   - Specialized templates for specific business models
   - Modular design for easy updates and expansion

## PHP Implementation Details

The implementation will use these core classes:

1. **`LegalPageForm`** - Handles the multi-step form process:
   - Step 1: Select legal page type
   - Step 2: Choose website type
   - Step 3: Customize content
   - Step 4: Preview and generate

2. **`LegalPageGenerator`** - Processes templates and replaces placeholders:
   - Loads templates based on website type
   - Replaces placeholders with custom values
   - Processes conditional sections
   - Converts markdown to HTML

3. **`LegalPageTemplate`** - Manages template information:
   - Lists available templates
   - Provides descriptions and metadata
   - Helps users choose appropriate templates

4. **`LegalPageConfig`** - Handles configuration:
   - Reads from your existing config system
   - Pre-fills common values
   - Stores user preferences

5. **`LegalContentPresets`** - Provides default content:
   - Website-specific default values
   - Legally appropriate language
   - Common settings for each business type

6. **`LegalPageController`** - Processes AJAX requests:
   - Handles form submissions
   - Generates previews
   - Creates final documents

## Next Steps

To implement this system:

1. **Copy the Template Files**: Move the created markdown templates to your project

2. **Create the PHP Classes**: Implement the classes described in the implementation guide

3. **Build the Frontend**: Create the multi-step form with Bootstrap 5

4. **Test with Different Website Types**: Ensure the generated documents are appropriate for each type

5. **Review with Legal Counsel**: Have the templates reviewed by a qualified attorney

## Find and Replace Cheat Sheet

When implementing this system, you'll need to replace the placeholders with actual values. The key placeholders are:

- `{{company:name}}` - Your company or website name
- `{{company:email}}` - Contact email address
- `{{company:address}}` - Physical business address
- `{{website:url}}` - Your full website URL
- `{{website:domain}}` - Just the domain name
- `{{data:collected}}` - Types of data you collect
- `{{data:retention}}` - How long you keep user data

Refer to the `/legal/placeholders/cheat-sheet.md` file for a complete list of placeholders organized by template type.

Would you like me to explain any specific part of the system in more detail?
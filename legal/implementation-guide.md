# Legal Page Generator Implementation Guide

This document provides guidance for implementing the `Y0hn\Gens\FooterPages` namespace classes for generating legal pages from the markdown templates.

## Core Classes

The implementation should include the following core classes:

### 1. `LegalPageForm`

This class handles the multi-step form for creating legal pages:

```php
<?php
namespace Y0hn\Gens\FooterPages;

class LegalPageForm
{
    private $currentStep = 1;
    private $maxSteps = 4;
    private $formData = [];

    public function __construct(array $initialData = [])
    {
        $this->formData = $initialData;
    }

    public function getCurrentStep()
    {
        return $this->currentStep;
    }

    public function setCurrentStep(int $step)
    {
        if ($step >= 1 && $step <= $this->maxSteps) {
            $this->currentStep = $step;
        }
        return $this;
    }

    public function nextStep()
    {
        if ($this->currentStep < $this->maxSteps) {
            $this->currentStep++;
        }
        return $this;
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
        return $this;
    }

    public function setFormData(array $data)
    {
        $this->formData = array_merge($this->formData, $data);
        return $this;
    }

    public function getFormData()
    {
        return $this->formData;
    }

    public function renderStep()
    {
        // Render the current step template
        switch ($this->currentStep) {
            case 1:
                return $this->renderPageTypeSelection();
            case 2:
                return $this->renderWebsiteTypeSelection();
            case 3:
                return $this->renderContentCustomization();
            case 4:
                return $this->renderPreviewAndGenerate();
            default:
                return '';
        }
    }

    private function renderPageTypeSelection()
    {
        // Implement HTML for selecting legal page type
    }

    private function renderWebsiteTypeSelection()
    {
        // Implement HTML for selecting website type
    }

    private function renderContentCustomization()
    {
        // Implement HTML for customizing content based on previous selections
    }

    private function renderPreviewAndGenerate()
    {
        // Implement HTML for previewing and generating the final page
    }

    public function validate()
    {
        // Implement validation for the current step
    }
}
```

### 2. `LegalPageGenerator`

This class handles the generation of legal pages from templates:

```php
<?php
namespace Y0hn\Gens\FooterPages;

class LegalPageGenerator
{
    private $websiteType;
    private $pageType;
    private $placeholders = [];
    private $configData = [];
    private $templatePath;

    public function __construct(string $pageType, string $websiteType, array $configData = [])
    {
        $this->pageType = $pageType;
        $this->websiteType = $websiteType;
        $this->configData = $configData;
        $this->templatePath = __DIR__ . '/../../legal';
        $this->loadDefaultPlaceholders();
    }

    private function loadDefaultPlaceholders()
    {
        // Load common placeholders from config
        foreach ($this->configData as $category => $fields) {
            foreach ($fields as $field => $value) {
                $this->placeholders["$category:$field"] = $value;
            }
        }

        // Add current date
        $this->placeholders['current:date'] = date('F j, Y');
    }

    public function setPlaceholder(string $category, string $field, string $value)
    {
        $this->placeholders["$category:$field"] = $value;
        return $this;
    }

    public function setPlaceholders(array $placeholders)
    {
        foreach ($placeholders as $key => $value) {
            if (strpos($key, ':') !== false) {
                $this->placeholders[$key] = $value;
            } else {
                // Handle flat array format
                $parts = explode('_', $key, 2);
                if (count($parts) === 2) {
                    $this->placeholders[$parts[0] . ':' . $parts[1]] = $value;
                }
            }
        }
        return $this;
    }

    public function generate()
    {
        // Find the template
        $templateFile = $this->findTemplate();

        if (!$templateFile) {
            throw new \Exception("Template for '{$this->pageType}' not found.");
        }

        // Read template content
        $content = file_get_contents($templateFile);

        // Replace placeholders
        $content = $this->replacePlaceholders($content);

        // Process conditional sections
        $content = $this->processConditionalSections($content);

        return $content;
    }

    private function findTemplate()
    {
        // Check for website-specific template first
        $specificPath = $this->templatePath . '/' . $this->websiteType . '/' . $this->pageType . '.md';

        if (file_exists($specificPath)) {
            return $specificPath;
        }

        // Fall back to base template
        $basePath = $this->templatePath . '/base/' . $this->pageType . '.md';

        if (file_exists($basePath)) {
            return $basePath;
        }

        return null;
    }

    private function replacePlaceholders(string $content)
    {
        foreach ($this->placeholders as $placeholder => $value) {
            $content = str_replace('{{' . $placeholder . '}}', $value, $content);
        }

        return $content;
    }

    private function processConditionalSections(string $content)
    {
        // Process if/endif sections
        preg_match_all('/{{if:(.*?)}}(.*?){{endif}}/s', $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $condition = $match[1];
            $sectionContent = $match[2];

            // Check if condition is met
            $include = false;

            if ($condition === $this->websiteType) {
                $include = true;
            } elseif (isset($this->placeholders["compliance:$condition"]) &&
                      $this->placeholders["compliance:$condition"] === 'true') {
                $include = true;
            } elseif (isset($this->placeholders["feature:$condition"]) &&
                      $this->placeholders["feature:$condition"] === 'true') {
                $include = true;
            }

            if ($include) {
                // Keep the content but remove the conditional tags
                $content = str_replace($match[0], $sectionContent, $content);
            } else {
                // Remove the section
                $content = str_replace($match[0], '', $content);
            }
        }

        return $content;
    }

    public function convertToHtml()
    {
        // Convert markdown to HTML using League\CommonMark
        $markdown = $this->generate();

        $converter = new \League\CommonMark\CommonMarkConverter();
        return $converter->convert($markdown);
    }
}
```

### 3. `LegalPageTemplate`

This class handles loading and managing templates:

```php
<?php
namespace Y0hn\Gens\FooterPages;

class LegalPageTemplate
{
    private $basePath;

    public function __construct()
    {
        $this->basePath = __DIR__ . '/../../legal';
    }

    public function getAvailablePageTypes()
    {
        $pageTypes = [];

        // Get all base templates
        $baseDir = $this->basePath . '/base';
        if (is_dir($baseDir)) {
            $files = scandir($baseDir);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'md') {
                    $pageTypes[] = pathinfo($file, PATHINFO_FILENAME);
                }
            }
        }

        // Get website-specific templates
        $websiteTypes = ['personal', 'ecommerce', 'social'];
        foreach ($websiteTypes as $type) {
            $typeDir = $this->basePath . '/' . $type;
            if (is_dir($typeDir)) {
                $files = scandir($typeDir);
                foreach ($files as $file) {
                    if (pathinfo($file, PATHINFO_EXTENSION) === 'md') {
                        $pageType = pathinfo($file, PATHINFO_FILENAME);
                        if (!in_array($pageType, $pageTypes)) {
                            $pageTypes[] = $pageType;
                        }
                    }
                }
            }
        }

        sort($pageTypes);
        return $pageTypes;
    }

    public function getTemplateInfo(string $pageType)
    {
        $info = [
            'name' => $this->formatName($pageType),
            'description' => $this->getTemplateDescription($pageType),
            'websiteTypes' => []
        ];

        // Check which website types have this template
        $websiteTypes = ['personal', 'ecommerce', 'social'];
        foreach ($websiteTypes as $type) {
            $path = $this->basePath . '/' . $type . '/' . $pageType . '.md';
            if (file_exists($path)) {
                $info['websiteTypes'][] = $type;
            }
        }

        // Add base template
        $basePath = $this->basePath . '/base/' . $pageType . '.md';
        if (file_exists($basePath)) {
            $info['hasBaseTemplate'] = true;
        } else {
            $info['hasBaseTemplate'] = false;
        }

        return $info;
    }

    private function formatName(string $pageType)
    {
        // Convert kebab-case to Title Case
        return ucwords(str_replace('-', ' ', $pageType));
    }

    private function getTemplateDescription(string $pageType)
    {
        // Provide descriptions for common legal page types
        $descriptions = [
            'privacy-policy' => 'Explains how you collect, use, and share user data.',
            'terms-of-service' => 'Sets the rules users agree to when using your site.',
            'cookie-policy' => 'Details the cookies your site uses and their purposes.',
            'refund-policy' => 'Outlines your return and refund processes.',
            'shipping-policy' => 'Explains your shipping methods, timeframes, and costs.',
            'dmca-policy' => 'Details how you handle copyright infringement claims.',
            'accessibility-statement' => 'Explains your commitment to digital accessibility.',
            'content-policy' => 'Sets guidelines for user-generated content.',
            'blog-disclaimer' => 'Clarifies limitations of blog content and your liability.'
        ];

        return $descriptions[$pageType] ?? 'Legal document for your website.';
    }
}
```

### 4. `LegalPageConfig`

This class handles reading from config files:

```php
<?php
namespace Y0hn\Gens\FooterPages;

use Yohns\Config\Config;

class LegalPageConfig
{
    private $config;

    public function __construct()
    {
        $this->config = new Config();
    }

    public function getDefaultPlaceholders()
    {
        $placeholders = [];

        // Company information
        if ($this->config->has('company')) {
            $company = $this->config->get('company');
            foreach ($company as $key => $value) {
                $placeholders["company:$key"] = $value;
            }
        }

        // Website information
        if ($this->config->has('website')) {
            $website = $this->config->get('website');
            foreach ($website as $key => $value) {
                $placeholders["website:$key"] = $value;
            }
        }

        // Data handling information
        if ($this->config->has('data')) {
            $data = $this->config->get('data');
            foreach ($data as $key => $value) {
                $placeholders["data:$key"] = $value;
            }
        }

        // E-commerce information
        if ($this->config->has('ecommerce')) {
            $ecommerce = $this->config->get('ecommerce');
            foreach ($ecommerce as $key => $value) {
                $placeholders["ecommerce:$key"] = $value;
            }
        }

        // Social network information
        if ($this->config->has('social')) {
            $social = $this->config->get('social');
            foreach ($social as $key => $value) {
                $placeholders["social:$key"] = $value;
            }
        }

        // Compliance information
        if ($this->config->has('compliance')) {
            $compliance = $this->config->get('compliance');
            foreach ($compliance as $key => $value) {
                $placeholders["compliance:$key"] = $value;
            }
        }

        return $placeholders;
    }

    public function getWebsiteTypes()
    {
        return [
            'personal' => 'Personal Blog or Website',
            'ecommerce' => 'Online Shop (USA Only)',
            'social' => 'Social Network or Community'
        ];
    }

    public function getPageTypes()
    {
        return [
            'privacy-policy' => 'Privacy Policy',
            'terms-of-service' => 'Terms of Service',
            'cookie-policy' => 'Cookie Policy',
            'dmca-policy' => 'DMCA Policy',
            'refund-policy' => 'Refund Policy',
            'shipping-policy' => 'Shipping Policy',
            'accessibility-statement' => 'Accessibility Statement',
            'content-policy' => 'Content Policy',
            'blog-disclaimer' => 'Blog Disclaimer'
        ];
    }
}
```

### 5. `LegalContentPresets`

This class handles the default content for different website types:

```php
<?php
namespace Y0hn\Gens\FooterPages;

class LegalContentPresets
{
    public function getPresetsForWebsiteType(string $websiteType)
    {
        switch ($websiteType) {
            case 'personal':
                return $this->getPersonalPresets();
            case 'ecommerce':
                return $this->getEcommercePresets();
            case 'social':
                return $this->getSocialPresets();
            default:
                return [];
        }
    }

    private function getPersonalPresets()
    {
        return [
            'data:collected' => 'name, email address, IP address',
            'data:cookies' => 'essential, analytics',
            'data:retention' => '12 months',
            'data:sharing' => 'analytics providers',
            'data:location' => 'United States',
            'compliance:gdpr' => 'false',
            'compliance:ccpa' => 'false'
        ];
    }

    private function getEcommercePresets()
    {
        return [
            'data:collected' => 'name, email address, billing address, shipping address, payment details, purchase history, IP address',
            'data:cookies' => 'essential, analytics, marketing, functional',
            'data:retention' => '36 months',
            'data:sharing' => 'payment processors, shipping providers, analytics providers',
            'data:location' => 'United States',
            'ecommerce:payment_processors' => 'Credit/Debit Cards, PayPal',
            'ecommerce:shipping_providers' => 'USPS, UPS, FedEx',
            'ecommerce:return_period' => '30 days',
            'ecommerce:shipping_time' => '3-5 business days',
            'ecommerce:refund_time' => '7-10 business days',
            'compliance:gdpr' => 'false',
            'compliance:ccpa' => 'true'
        ];
    }

    private function getSocialPresets()
    {
        return [
            'data:collected' => 'name, email address, profile information, content posted, connections, messaging history, IP address, device information',
            'data:cookies' => 'essential, analytics, functional, targeting, social media',
            'data:retention' => '48 months',
            'data:sharing' => 'analytics providers, advertising partners, third-party app integrations',
            'data:location' => 'United States',
            'social:content_policy' => 'hate speech, harassment, illegal activities, graphic violence, adult content, spam',
            'social:minimum_age' => '13',
            'social:reporting' => 'report button on content, email to support@example.com',
            'social:account_termination' => 'three violations of our content policy',
            'social:content_rights' => 'non-exclusive license to use, modify, and distribute your content on our platform',
            'compliance:gdpr' => 'true',
            'compliance:ccpa' => 'true',
            'compliance:coppa' => 'true'
        ];
    }
}
```

### 6. `LegalPageController`

This class handles the AJAX requests for the form:

```php
<?php
namespace Y0hn\Gens\FooterPages;

class LegalPageController
{
    private $form;
    private $config;
    private $template;
    private $presets;

    public function __construct()
    {
        $this->form = new LegalPageForm();
        $this->config = new LegalPageConfig();
        $this->template = new LegalPageTemplate();
        $this->presets = new LegalContentPresets();
    }

    public function handleRequest()
    {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'init':
                return $this->initForm();
            case 'next_step':
                return $this->nextStep();
            case 'previous_step':
                return $this->previousStep();
            case 'get_website_presets':
                return $this->getWebsitePresets();
            case 'generate_preview':
                return $this->generatePreview();
            case 'generate_final':
                return $this->generateFinal();
            default:
                return [
                    'success' => false,
                    'message' => 'Invalid action'
                ];
        }
    }

    private function initForm()
    {
        $defaultData = $this->config->getDefaultPlaceholders();
        $this->form->setFormData($defaultData);

        return [
            'success' => true,
            'step' => 1,
            'html' => $this->form->renderStep(),
            'formData' => $this->form->getFormData()
        ];
    }

    private function nextStep()
    {
        // Validate current step
        $isValid = $this->form->validate();

        if (!$isValid) {
            return [
                'success' => false,
                'message' => 'Please complete all required fields.',
                'errors' => $this->form->getErrors()
            ];
        }

        // Update form data
        $formData = $_POST['formData'] ?? [];
        $this->form->setFormData($formData);

        // Move to next step
        $this->form->nextStep();

        return [
            'success' => true,
            'step' => $this->form->getCurrentStep(),
            'html' => $this->form->renderStep(),
            'formData' => $this->form->getFormData()
        ];
    }

    private function previousStep()
    {
        // Update form data
        $formData = $_POST['formData'] ?? [];
        $this->form->setFormData($formData);

        // Move to previous step
        $this->form->previousStep();

        return [
            'success' => true,
            'step' => $this->form->getCurrentStep(),
            'html' => $this->form->renderStep(),
            'formData' => $this->form->getFormData()
        ];
    }

    private function getWebsitePresets()
    {
        $websiteType = $_POST['websiteType'] ?? '';
        $presets = $this->presets->getPresetsForWebsiteType($websiteType);

        return [
            'success' => true,
            'presets' => $presets
        ];
    }

    private function generatePreview()
    {
        $formData = $_POST['formData'] ?? [];
        $this->form->setFormData($formData);

        $pageType = $formData['pageType'] ?? '';
        $websiteType = $formData['websiteType'] ?? '';

        $generator = new LegalPageGenerator($pageType, $websiteType, $formData);

        try {
            $markdown = $generator->generate();
            $html = $generator->convertToHtml();

            return [
                'success' => true,
                'markdown' => $markdown,
                'html' => $html
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function generateFinal()
    {
        $formData = $_POST['formData'] ?? [];
        $this->form->setFormData($formData);

        $pageType = $formData['pageType'] ?? '';
        $websiteType = $formData['websiteType'] ?? '';

        $generator = new LegalPageGenerator($pageType, $websiteType, $formData);

        try {
            $markdown = $generator->generate();
            $html = $generator->convertToHtml();

            // Save the generated content
            $filename = $pageType . '.md';
            $filepath = '/path/to/generated/legal/' . $filename;
            file_put_contents($filepath, $markdown);

            return [
                'success' => true,
                'message' => 'Legal page generated successfully!',
                'filename' => $filename,
                'filepath' => $filepath
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
```

## Frontend Implementation

The frontend implementation should include:

1. A multi-step form with:
   - Step 1: Select legal page type
   - Step 2: Select website type
   - Step 3: Customize content (pre-filled based on website type)
   - Step 4: Preview and generate

2. JavaScript for form handling:
   - AJAX requests to the backend
   - Form validation
   - Dynamic content loading
   - Preview functionality

3. CSS for styling:
   - Bootstrap 5 for responsive design
   - Progress indicator for the multi-step form
   - Preview styling

## Security Considerations

1. Input validation:
   - Validate all user inputs on both client and server side
   - Sanitize inputs before processing
   - Restrict file operations to the designated directory

2. AJAX security:
   - Validate request sources
   - Implement CSRF protection
   - Rate limit requests

3. Output safety:
   - Escape HTML entities in user-provided content
   - Sanitize markdown output
   - Validate generated content

## Best Practices

1. Use a consistent naming convention for placeholders
2. Organize templates by website type
3. Provide clear instructions and tooltips in the form
4. Allow users to save and load form data
5. Implement version control for generated legal documents
6. Provide a disclaimer about seeking legal advice
7. Regularly update templates to reflect current laws and regulations
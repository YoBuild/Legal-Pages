<?php
// src/Yohns/Gens/Legal/LegalPageTemplate.php

namespace Yohns\Gens\Legal;

/**
 * LegalPageTemplate - Manages storage and retrieval of legal page templates
 *
 * This class handles the storage and retrieval of template files, including
 * creating default templates if they don't exist.
 *
 * @package Yohns\Gens\Legal
 */
class LegalPageTemplate {
	/** @var string The template directory path */
	private string $templateDir;

	/** @var array Template definitions and their default content */
	private array $defaultTemplates = [];

	/**
	 * Constructor - Initializes the template manager
	 *
	 * @param string $templateDir The template directory path
	 */
	public function __construct(string $templateDir = '') {
		// Set the template directory, defaulting to a subdirectory if not provided
		$this->templateDir = $templateDir ?: __DIR__ . '/templates';

		// Ensure the template directory exists
		$this->ensureTemplateDirectoryExists();

		// Initialize default templates
		$this->initializeDefaultTemplates();
	}

	/**
	 * Ensure the template directory exists
	 *
	 * @return void
	 */
	private function ensureTemplateDirectoryExists(): void {
		if (!is_dir($this->templateDir)) {
			if (!mkdir($this->templateDir, 0755, true)) {
				throw new \RuntimeException("Failed to create template directory: {$this->templateDir}");
			}
		}
	}

	/**
	 * Initialize the default template definitions
	 *
	 * @return void
	 */
	private function initializeDefaultTemplates(): void {
		// This would be much more comprehensive in a real implementation
		// Here we're just defining basic structures for a few key templates

		// Terms of Service template
		$this->defaultTemplates['terms'] = <<<HTML
<!DOCTYPE html>
<html>
<head>
	<title>Terms of Service - {{website_name}}</title>
</head>
<body>
	<h1>Terms of Service</h1>
	<p>Last Updated: {{last_updated}}</p>

	<p>Welcome to {{website_name}} (the "Website"), operated by {{company_name}} ("we," "us," or "our").</p>

	<h2>1. Acceptance of Terms</h2>
	<p>By accessing or using the Website, you agree to be bound by these Terms of Service ("Terms") and our Privacy Policy. If you do not agree to these Terms, you may not access or use the Website.</p>

	<h2>2. Changes to Terms</h2>
	<p>We reserve the right to modify these Terms at any time. We will provide notice of any material changes by updating the "Last Updated" date at the top of these Terms. Your continued use of the Website after such changes constitutes your acceptance of the new Terms.</p>

	<h2>3. User Accounts</h2>
	<p>{{user_account_terms}}</p>

	<h2>4. Prohibited Activities</h2>
	<p>{{prohibited_activities}}</p>

	<h2>5. Intellectual Property</h2>
	<p>{{intellectual_property_terms}}</p>

	<h2>6. Disclaimer of Warranties</h2>
	<p>{{disclaimer_terms}}</p>

	<h2>7. Limitation of Liability</h2>
	<p>{{limitation_liability_terms}}</p>

	<h2>8. Indemnification</h2>
	<p>{{indemnification_terms}}</p>

	<h2>9. Governing Law</h2>
	<p>{{governing_law_terms}}</p>

	<h2>10. Dispute Resolution</h2>
	<p>{{dispute_resolution_terms}}</p>

	<h2>11. Termination</h2>
	<p>{{termination_terms}}</p>

	<h2>12. Miscellaneous</h2>
	<p>{{miscellaneous_terms}}</p>

	<h2>13. Contact Information</h2>
	<p>If you have any questions about these Terms, please contact us at {{company_email}}.</p>
</body>
</html>
HTML;

		// Privacy Policy template
		$this->defaultTemplates['privacy'] = <<<HTML
<!DOCTYPE html>
<html>
<head>
	<title>Privacy Policy - {{website_name}}</title>
</head>
<body>
	<h1>Privacy Policy</h1>
	<p>Effective Date: {{effective_date}}</p>

	<p>{{company_name}} ("we," "us," or "our") respects your privacy and is committed to protecting your personal information.</p>

	<h2>1. Information We Collect</h2>
	<p>{{information_collected}}</p>

	<h2>2. How We Use Your Information</h2>
	<p>{{information_usage}}</p>

	<h2>3. Information Sharing and Disclosure</h2>
	<p>{{information_sharing}}</p>

	<h2>4. Cookies and Similar Technologies</h2>
	<p>{{cookies_information}}</p>

	<h2>5. Data Security</h2>
	<p>{{data_security}}</p>

	<h2>6. Your Rights and Choices</h2>
	<p>{{user_rights}}</p>

	<h2>7. Children's Privacy</h2>
	<p>{{children_privacy}}</p>

	<h2>8. Third-Party Links</h2>
	<p>{{third_party_links}}</p>

	<h2>9. Changes to This Privacy Policy</h2>
	<p>{{policy_changes}}</p>

	<h2>10. International Data Transfers</h2>
	<p>{{international_transfers}}</p>

	<h2>11. Contact Us</h2>
	<p>If you have any questions about this Privacy Policy, please contact us at:</p>
	<p>{{company_name}}<br>
	   {{company_address}}<br>
	   Email: {{company_email}}</p>
</body>
</html>
HTML;

		// Cookie Policy template
		$this->defaultTemplates['cookies'] = <<<HTML
<!DOCTYPE html>
<html>
<head>
	<title>Cookie Policy - {{website_name}}</title>
</head>
<body>
	<h1>Cookie Policy</h1>
	<p>Last Updated: {{last_updated}}</p>

	<h2>1. What Are Cookies</h2>
	<p>{{what_are_cookies}}</p>

	<h2>2. How We Use Cookies</h2>
	<p>{{cookies_usage}}</p>

	<h2>3. Types of Cookies We Use</h2>
	<p>{{cookies_types}}</p>

	<h2>4. Managing Cookies</h2>
	<p>{{managing_cookies}}</p>

	<h2>5. Changes to This Cookie Policy</h2>
	<p>{{policy_changes}}</p>

	<h2>6. Contact Us</h2>
	<p>If you have any questions about this Cookie Policy, please contact us at {{company_email}}.</p>
</body>
</html>
HTML;
	}

	/**
	 * Get the list of available templates
	 *
	 * @return array Associative array of template name => display name
	 */
	public function getAvailableTemplates(): array {
		$templates = [];

		// First get all default templates
		foreach (array_keys($this->defaultTemplates) as $templateName) {
			$displayName = ucwords(str_replace('_', ' ', $templateName));
			$templates[$templateName] = $displayName;
		}

		// Then scan the template directory for additional templates
		if (is_dir($this->templateDir)) {
			foreach (scandir($this->templateDir) as $file) {
				// Skip . and .. entries
				if ($file === '.' || $file === '..') {
					continue;
				}

				// Skip non-HTML files
				if (!preg_match('/\.html$/', $file)) {
					continue;
				}

				// Get template name (filename without extension)
				$templateName = pathinfo($file, PATHINFO_FILENAME);

				// Only add if not already in the list
				if (!isset($templates[$templateName])) {
					$displayName = ucwords(str_replace('_', ' ', $templateName));
					$templates[$templateName] = $displayName;
				}
			}
		}

		return $templates;
	}

	/**
	 * Get a template by name
	 *
	 * If the template doesn't exist, it will be created from the default template
	 *
	 * @param string $templateName The template name
	 * @return string The template content
	 * @throws \InvalidArgumentException If the template doesn't exist and no default is available
	 */
	public function getTemplate(string $templateName): string {
		$templatePath = "{$this->templateDir}/{$templateName}.html";

		// If the template file exists, return its content
		if (file_exists($templatePath)) {
			return file_get_contents($templatePath);
		}

		// If a default template is available, create it and return the content
		if (isset($this->defaultTemplates[$templateName])) {
			$this->saveTemplate($templateName, $this->defaultTemplates[$templateName]);
			return $this->defaultTemplates[$templateName];
		}

		// No template exists and no default is available
		throw new \InvalidArgumentException("Template not found: {$templateName}");
	}

	/**
	 * Save a template
	 *
	 * @param string $templateName The template name
	 * @param string $content The template content
	 * @return bool True if the template was saved successfully
	 */
	public function saveTemplate(string $templateName, string $content): bool {
		$templatePath = "{$this->templateDir}/{$templateName}.html";

		// Ensure template directory exists
		$this->ensureTemplateDirectoryExists();

		// Save the template file
		return file_put_contents($templatePath, $content) !== false;
	}

	/**
	 * Delete a template
	 *
	 * @param string $templateName The template name
	 * @return bool True if the template was deleted successfully
	 */
	public function deleteTemplate(string $templateName): bool {
		$templatePath = "{$this->templateDir}/{$templateName}.html";

		// Check if the template exists
		if (!file_exists($templatePath)) {
			return false;
		}

		// Delete the template file
		return unlink($templatePath);
	}

	/**
	 * Extract placeholders from a template
	 *
	 * @param string $templateContent The template content
	 * @return array Array of placeholders found in the template
	 */
	public function extractPlaceholders(string $templateContent): array {
		// Extract placeholders using regex - looking for {{placeholder}} format
		preg_match_all('/\{\{([a-zA-Z0-9_]+)\}\}/', $templateContent, $matches);

		if (empty($matches[1])) {
			return [];
		}

		// Return unique placeholders
		return array_unique($matches[1]);
	}
}

<?php
// src/Yohns/Gens/Legal/LegalPageGenerator.php

namespace Yohns\Gens\Legal;

use Yohns\Core\Config;

/**
 * LegalPageGenerator - Handles template loading and legal page generation
 *
 * Merged class combining markdown-first flow (FooterPages) with template management,
 * file saving, and conditional processing.
 *
 * Supports both Markdown (.md) and HTML (.html) templates with placeholder replacement
 * and conditional section handling for {{if:token}}...{{endif}} blocks.
 *
 * @package Yohns\Gens\Legal
 */
class LegalPageGenerator {
	/** @var string The page type (e.g., 'privacy-policy', 'terms-of-service') */
	private string $pageType;

	/** @var string The website type (e.g., 'personal', 'ecommerce', 'social') */
	private string $websiteType;

	/** @var string The template directory path */
	private string $templateDir;

	/** @var array Placeholder key-value pairs (uses both formats: 'category:field' and 'placeholder_name') */
	private array $placeholders = [];

	/** @var array Config data passed to constructor */
	private array $configData = [];

	/** @var array Common substitutions loaded from Yohns\Core\Config */
	private array $commonSubstitutions = [];

	/**
	 * Constructor - Initializes the legal page generator
	 *
	 * @param string $pageType The page type (without extension)
	 * @param string $websiteType The website type (e.g., 'personal', 'ecommerce', 'social')
	 * @param array $configData Additional config data for placeholders
	 * @param string $templateDir Optional custom template directory path
	 */
	public function __construct(
		string $pageType = '',
		string $websiteType = '',
		array $configData = [],
		string $templateDir = ''
	) {
		$this->pageType = $pageType;
		$this->websiteType = $websiteType;
		$this->configData = $configData;

		// Set the template directory - resolve relative to project root
		if (!empty($templateDir)) {
			$this->templateDir = $templateDir;
		} else {
			// Default: look for legal/ in the project root
			// Yohns/Gens/Legal/ -> ../../legal
			$this->templateDir = realpath(__DIR__ . '/../../legal');
			if (!$this->templateDir) {
				// Fallback to absolute path from project root
				$projectRoot = dirname(__DIR__, 3);
				$this->templateDir = $projectRoot . '/legal';
			}
		}

		// Ensure the template directory exists
		if (!is_dir($this->templateDir)) {
			throw new \RuntimeException("Template directory not found: {$this->templateDir}");
		}

		// Load common substitutions from config and populate placeholders
		$this->loadCommonSubstitutions();
		$this->loadDefaultPlaceholders();
	}	/**
		 * Load common substitutions from Yohns\Core\Config
		 *
		 * @return void
		 */
	private function loadCommonSubstitutions(): void {
		// Try to get common information from config
		try {
			$this->commonSubstitutions = [
				'company:name'    => Config::get('company_name', 'site') ?? '',
				'company:address' => Config::get('company_address', 'site') ?? '',
				'company:email'   => Config::get('contact_email', 'site') ?? '',
				'company:phone'   => Config::get('contact_phone', 'site') ?? '',
				'company:country' => Config::get('company_country', 'site') ?? 'United States',
				'website:url'     => Config::get('site_url', 'site') ?? '',
				'website:name'    => Config::get('site_name', 'site') ?? '',
				'current:date'    => date('F j, Y'),
				'current:year'    => date('Y'),
			];
		} catch (\Exception $e) {
			// Config not available, use empty substitutions
			$this->commonSubstitutions = [
				'current:date' => date('F j, Y'),
				'current:year' => date('Y'),
			];
		}
	}

	/**
	 * Load default placeholders from configData and commonSubstitutions
	 *
	 * @return void
	 */
	private function loadDefaultPlaceholders(): void {
		// Start with common substitutions
		$this->placeholders = $this->commonSubstitutions;

		// Merge in config data (using colon format: category:field)
		foreach ($this->configData as $category => $fields) {
			if (is_array($fields)) {
				foreach ($fields as $field => $value) {
					$this->placeholders["$category:$field"] = $value;
				}
			} else {
				// Handle flat structure
				$this->placeholders[$category] = $fields;
			}
		}
	}

	/**
	 * Set a single placeholder value
	 *
	 * @param string $category The placeholder category or key (e.g., 'company' or 'company:name')
	 * @param string $field Optional field name if using category:field format
	 * @param string $value The value to set
	 * @return self
	 */
	public function setPlaceholder(string $category, ?string $field = null, ?string $value = null): self {
		// Handle both setPlaceholder('company', 'name', 'ACME') and setPlaceholder('company:name', 'ACME')
		if ($field === null) {
			// Assume $category is 'company:name' and $field is the value
			$this->placeholders[$category] = $value ?? '';
		} else {
			$this->placeholders["$category:$field"] = $value ?? '';
		}
		return $this;
	}

	/**
	 * Set multiple placeholders at once
	 *
	 * Accepts both colon-format (category:field) and underscore-format (category_field) keys,
	 * and normalizes them to colon format internally.
	 *
	 * @param array $placeholders Key-value pairs of placeholders
	 * @return self
	 */
	public function setPlaceholders(array $placeholders): self {
		foreach ($placeholders as $key => $value) {
			if (strpos($key, ':') !== false) {
				// Already in colon format
				$this->placeholders[$key] = $value;
			} elseif (strpos($key, '_') !== false) {
				// Convert underscore format to colon format
				$parts = explode('_', $key, 2);
				if (count($parts) === 2) {
					$this->placeholders[$parts[0] . ':' . $parts[1]] = $value;
				} else {
					$this->placeholders[$key] = $value;
				}
			} else {
				// Single key, store as-is
				$this->placeholders[$key] = $value;
			}
		}
		return $this;
	}

	/**
	 * Get all current placeholders
	 *
	 * @return array
	 */
	public function getPlaceholders(): array {
		return $this->placeholders;
	}

	/**
	 * Get available legal page template types from the template directory
	 *
	 * Scans for both .md (markdown) and .html files in base/ and website-type subdirectories.
	 *
	 * @return array Associative array of template types and their display names
	 */
	public function getAvailableTemplates(): array {
		$templates = [];

		// Get all base templates
		$baseDir = $this->templateDir . '/base';
		if (is_dir($baseDir)) {
			foreach (scandir($baseDir) as $file) {
				if (preg_match('/\.(md|html)$/', $file)) {
					$key = pathinfo($file, PATHINFO_FILENAME);
					if (!isset($templates[$key])) {
						$templates[$key] = $this->formatName($key);
					}
				}
			}
		}

		// Get website-specific templates
		$websiteTypes = ['personal', 'ecommerce', 'social'];
		foreach ($websiteTypes as $type) {
			$typeDir = $this->templateDir . '/' . $type;
			if (is_dir($typeDir)) {
				foreach (scandir($typeDir) as $file) {
					if (preg_match('/\.(md|html)$/', $file)) {
						$key = pathinfo($file, PATHINFO_FILENAME);
						if (!isset($templates[$key])) {
							$templates[$key] = $this->formatName($key);
						}
					}
				}
			}
		}

		sort($templates);
		return $templates;
	}

	/**
	 * Format a page type name (kebab-case) to Title Case
	 *
	 * @param string $pageType The page type (e.g., 'privacy-policy')
	 * @return string Formatted name (e.g., 'Privacy Policy')
	 */
	private function formatName(string $pageType): string {
		return ucwords(str_replace('-', ' ', $pageType));
	}

	/**
	 * Get the path to a template file
	 *
	 * Looks for website-specific template first (legal/{websiteType}/{pageType}.md),
	 * then falls back to base template (legal/base/{pageType}.md).
	 * Also supports .html extension.
	 *
	 * @return string|null The path to the template file, or null if not found
	 */
	public function findTemplate(): ?string {
		// Check for website-specific markdown template first
		$specificPath = $this->templateDir . '/' . $this->websiteType . '/' . $this->pageType . '.md';
		if (file_exists($specificPath)) {
			return $specificPath;
		}

		// Check for website-specific HTML template
		$specificHtmlPath = $this->templateDir . '/' . $this->websiteType . '/' . $this->pageType . '.html';
		if (file_exists($specificHtmlPath)) {
			return $specificHtmlPath;
		}

		// Fall back to base markdown template
		$basePath = $this->templateDir . '/base/' . $this->pageType . '.md';
		if (file_exists($basePath)) {
			return $basePath;
		}

		// Fall back to base HTML template
		$baseHtmlPath = $this->templateDir . '/base/' . $this->pageType . '.html';
		if (file_exists($baseHtmlPath)) {
			return $baseHtmlPath;
		}

		return null;
	}

	/**
	 * Extract all placeholders from template content
	 *
	 * Returns placeholders found as {{placeholder}} in colon format (category:field).
	 *
	 * @param string $content The template content
	 * @return array Array of unique placeholder keys
	 */
	public function extractPlaceholders(string $content): array {
		// Extract placeholders using regex - looking for {{placeholder}} format
		preg_match_all('/\{\{([a-zA-Z0-9_:]+)\}\}/', $content, $matches);

		if (empty($matches[1])) {
			return [];
		}

		// Return unique placeholders, normalized to colon format
		$placeholders = [];
		foreach (array_unique($matches[1]) as $placeholder) {
			// Skip control directives
			if (strpos($placeholder, 'if:') === 0 || strpos($placeholder, 'endif') === 0) {
				continue;
			}

			// Normalize underscore format to colon format
			if (strpos($placeholder, ':') === false && strpos($placeholder, '_') !== false) {
				$parts = explode('_', $placeholder, 2);
				if (count($parts) === 2) {
					$placeholder = $parts[0] . ':' . $parts[1];
				}
			}

			$placeholders[] = $placeholder;
		}

		return array_unique($placeholders);
	}

	/**
	 * Generate the legal page content by loading template and replacing placeholders
	 *
	 * Applies placeholder substitution and processes conditional blocks.
	 *
	 * @return string The generated page content (markdown or HTML depending on template)
	 * @throws \Exception If template not found or generation fails
	 */
	public function generate(): string {
		// Find the template
		$templateFile = $this->findTemplate();

		if (!$templateFile) {
			throw new \Exception("Template for '{$this->pageType}' not found.");
		}

		// Read template content
		$content = file_get_contents($templateFile);

		// Replace placeholders
		$content = $this->replacePlaceholders($content);

		// Process conditional sections (only for markdown templates)
		if (pathinfo($templateFile, PATHINFO_EXTENSION) === 'md') {
			$content = $this->processConditionalSections($content);
		}

		return $content;
	}

	/**
	 * Replace all placeholders in content with their values
	 *
	 * Supports both colon-format ({{company:name}}) and underscore-format ({{company_name}}) placeholders.
	 *
	 * @param string $content The content with placeholders
	 * @return string Content with placeholders replaced
	 */
	private function replacePlaceholders(string $content): string {
		foreach ($this->placeholders as $placeholder => $value) {
			// Replace colon-format placeholders
			$content = str_replace('{{' . $placeholder . '}}', $value, $content);

			// Also try underscore format for compatibility
			$underscore = str_replace(':', '_', $placeholder);
			if ($underscore !== $placeholder) {
				$content = str_replace('{{' . $underscore . '}}', $value, $content);
			}
		}

		return $content;
	}

	/**
	 * Process conditional sections in markdown templates
	 *
	 * Handles {{if:token}}...{{endif}} blocks.
	 * token can be:
	 * - A website type (personal, ecommerce, social)
	 * - A compliance flag (gdpr, ccpa, coppa, ada)
	 * - A feature key tested against placeholders
	 *
	 * @param string $content The content with conditionals
	 * @return string Content with conditionals processed
	 */
	private function processConditionalSections(string $content): string {
		// Process if/endif sections
		preg_match_all('/\{\{if:(.*?)\}\}(.*?)\{\{endif\}\}/s', $content, $matches, PREG_SET_ORDER);

		foreach ($matches as $match) {
			$condition = trim($match[1]);
			$sectionContent = $match[2];

			// Check if condition is met
			$include = false;

			// Check if condition is website type
			if ($condition === $this->websiteType) {
				$include = true;
			}
			// Check if condition is a compliance flag in placeholders
			elseif (isset($this->placeholders["compliance:$condition"])) {
				$value = $this->placeholders["compliance:$condition"];
				$include = ($value === 'true' || $value === '1' || $value === true);
			}
			// Check if condition is a feature flag in placeholders
			elseif (isset($this->placeholders["feature:$condition"])) {
				$value = $this->placeholders["feature:$condition"];
				$include = ($value === 'true' || $value === '1' || $value === true);
			}

			if ($include) {
				// Keep the content but remove the conditional tags
				$content = str_replace($match[0], $sectionContent, $content);
			} else {
				// Remove the entire section
				$content = str_replace($match[0], '', $content);
			}
		}

		return $content;
	}

	/**
	 * Convert generated markdown to HTML using League\CommonMark
	 *
	 * If the template was HTML, returns it as-is.
	 *
	 * @return string Generated HTML content
	 * @throws \Exception If conversion fails or template not found
	 */
	public function convertToHtml(): string {
		// Generate the base content
		$content = $this->generate();

		// Check if we're working with a markdown template
		$templateFile = $this->findTemplate();
		if ($templateFile && pathinfo($templateFile, PATHINFO_EXTENSION) === 'html') {
			// Already HTML, return as-is
			return $content;
		}

		// Convert markdown to HTML using League\CommonMark
		$converter = new \League\CommonMark\CommonMarkConverter();
		return $converter->convert($content)->getContent();
	}

	/**
	 * Save generated content to a file
	 *
	 * @param string $content The content to save
	 * @param string $filename The filename (will be validated and sanitized)
	 * @param string $outputDir The output directory
	 * @return bool True if saved successfully
	 * @throws \RuntimeException If directory creation or file write fails
	 */
	public function savePage(string $content, string $filename, string $outputDir): bool {
		// Ensure directory exists
		if (!is_dir($outputDir)) {
			if (!mkdir($outputDir, 0755, true)) {
				throw new \RuntimeException("Failed to create directory: {$outputDir}");
			}
		}

		// Full path
		$path = rtrim($outputDir, '/\\') . '/' . basename($filename);

		// Save the file
		if (file_put_contents($path, $content) === false) {
			throw new \RuntimeException("Failed to write to file: {$path}");
		}

		return true;
	}
}

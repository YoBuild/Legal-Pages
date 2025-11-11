<?php
// src/Y0hn/Gens/Legal/LegalPageForm.php

namespace Y0hn\Gens\Legal;

use Yohns\Core\Config;

/**
 * LegalPageForm - Handles generation and processing of forms for legal page creation
 *
 * This class generates forms for creating various legal documents such as
 * Terms of Service, Privacy Policies, etc. based on templates with placeholders.
 *
 * @package Y0hn\Gens\Legal
 */
class LegalPageForm {
	/** @var array The available legal page types */
	private array $availablePageTypes = [
		'terms'          => 'Terms of Service',
		'privacy'        => 'Privacy Policy',
		'cookies'        => 'Cookie Policy',
		'disclaimer'     => 'Disclaimer',
		'refund'         => 'Refund Policy',
		'shipping'       => 'Shipping Policy',
		'eula'           => 'End User License Agreement',
		'acceptable_use' => 'Acceptable Use Policy',
		'copyright'      => 'Copyright Notice',
		'dmca'           => 'DMCA Policy',
		'accessibility'  => 'Accessibility Statement',
		'gdpr'           => 'GDPR Compliance Statement',
		'ccpa'           => 'CCPA Compliance Statement'
	];

	/** @var array Default company information from config */
	private array $companyInfo = [];

	/** @var string The selected page type */
	private string $selectedPageType = '';

	/** @var array Placeholder data for the selected template */
	private array $placeholders = [];

	/** @var array Raw form data accumulated across steps */
	private array $formData = [];

	/** @var int Current step in multi-step form (1-based) */
	private int $currentStep = 1;

	/**
	 * Constructor - Initializes the form generator
	 *
	 * @param string $configDir The configuration directory path
	 */
	public function __construct(string $configDir = '') {
		// If Config is not already initialized, do it now
		//-if (empty($configDir) && !Config::isInitialized()) {
		//-	throw new \Exception('Config directory must be provided if Config is not already initialized');
		//-}

		if (!empty($configDir)) {
			new Config($configDir);
		}

		// Load company information from config
		$this->loadCompanyInfo();
	}

	/**
	 * Load company information from configuration
	 *
	 * @return void
	 */
	private function loadCompanyInfo(): void {
		// Try to get company information from config
		$this->companyInfo = [
			'company_name'    => Config::get('company_name', 'site') ?? '',
			'company_address' => Config::get('company_address', 'site') ?? '',
			'company_email'   => Config::get('contact_email', 'site') ?? '',
			'company_phone'   => Config::get('contact_phone', 'site') ?? '',
			'website_url'     => Config::get('site_url', 'site') ?? '',
			'website_name'    => Config::get('site_name', 'site') ?? '',
		];
	}

	/**
	 * Get the list of available legal page types
	 *
	 * @return array Associative array of page type => display name
	 */
	public function getAvailablePageTypes(): array {
		return $this->availablePageTypes;
	}

	/**
	 * Set the selected page type
	 *
	 * @param string $pageType The page type key
	 * @return self
	 * @throws \InvalidArgumentException If the page type is not valid
	 */
	public function setPageType(string $pageType): self {
		if (!array_key_exists($pageType, $this->availablePageTypes)) {
			throw new \InvalidArgumentException("Invalid page type: {$pageType}");
		}

		$this->selectedPageType = $pageType;
		$this->loadPlaceholders();

		return $this;
	}

	/**
	 * Get the currently selected page type
	 *
	 * @return string
	 */
	public function getSelectedPageType(): string {
		return $this->selectedPageType;
	}

	/**
	 * Load placeholders for the selected template
	 *
	 * @return void
	 */
	private function loadPlaceholders(): void {
		// Path to template file would typically be in a predefined location
		$templatePath = $this->getTemplatePath($this->selectedPageType);

		if (!file_exists($templatePath)) {
			throw new \RuntimeException("Template file not found: {$templatePath}");
		}

		// Read template content
		$content = file_get_contents($templatePath);

		// Extract placeholders using regex - looking for {{placeholder}} format
		preg_match_all('/\{\{([a-zA-Z0-9_]+)\}\}/', $content, $matches);

		if (empty($matches[1])) {
			return;
		}

		// Create placeholders array with default values from company info if available
		foreach ($matches[1] as $placeholder) {
			$this->placeholders[$placeholder] = $this->companyInfo[$placeholder] ?? '';
		}
	}

	/**
	 * Get path to the template file
	 *
	 * @param string $pageType The page type
	 * @return string The path to the template file
	 */
	private function getTemplatePath(string $pageType): string {
		// This would normally be determined by your application structure
		// For now, we'll assume templates are in a 'templates' directory
		$templatesDir = __DIR__ . '/templates';

		return "{$templatesDir}/{$pageType}.html";
	}

	/**
	 * Get the current placeholders
	 *
	 * @return array
	 */
	public function getPlaceholders(): array {
		return $this->placeholders;
	}

	/**
	 * Set form data (merge) and normalize into placeholders used by the form UI
	 *
	 * Accepts keys in colon-format (category:field), underscore-format (category_field)
	 * or plain keys (page_type, websiteType, etc.). Normalizes colon -> underscore
	 * for the internal placeholder map so generatePlaceholderForm() can render fields.
	 *
	 * @param array $data
	 * @return self
	 */
	public function setFormData(array $data): self {
		// Merge raw form data
		$this->formData = array_merge($this->formData, $data);

		// Normalize keys into placeholders for the UI
		foreach ($data as $key => $value) {
			// Normalize colon to underscore for template-style placeholders
			$normalized = str_replace(':', '_', $key);

			// Also normalize camelCase websiteType -> websiteType (leave as-is)
			$this->placeholders[$normalized] = $value;
		}

		return $this;
	}

	/**
	 * Get the accumulated form data for the multi-step flow
	 *
	 * @return array
	 */
	public function getFormData(): array {
		// Return raw form data merged with a few derived values for convenience
		$result = $this->formData;
		// expose current step
		$result['currentStep'] = $this->currentStep;
		return $result;
	}

	/**
	 * Advance to the next step
	 *
	 * @return self
	 */
	public function nextStep(): self {
		$this->currentStep = min($this->currentStep + 1, 4);
		return $this;
	}

	/**
	 * Move to the previous step
	 *
	 * @return self
	 */
	public function previousStep(): self {
		$this->currentStep = max($this->currentStep - 1, 1);
		return $this;
	}

	/**
	 * Get the current step number
	 *
	 * @return int
	 */
	public function getCurrentStep(): int {
		return $this->currentStep;
	}

	/**
	 * Generate the HTML form for the selected template
	 *
	 * @return string HTML form
	 */
	public function generateForm(): string {
		if (empty($this->selectedPageType)) {
			// Return page type selection form
			return $this->generatePageTypeForm();
		}

		// Return form with fields for all placeholders
		return $this->generatePlaceholderForm();
	}

	/**
	 * Generate the page type selection form
	 *
	 * @return string HTML form
	 */
	private function generatePageTypeForm(): string {
		$html = '<form method="post" class="legal-page-type-form" action="index.php">';
		$html .= '<input type="hidden" name="action" value="legal/select_type">';

		// Form title
		$html .= '<h2>Select Legal Page Type</h2>';

		// Page type dropdown
		$html .= '<div class="form-group mb-3">';
		$html .= '<label for="page_type">Page Type</label>';
		$html .= '<select name="page_type" id="page_type" class="form-control" required>';
		$html .= '<option value="">-- Select Page Type --</option>';

		foreach ($this->availablePageTypes as $key => $label) {
			$html .= "<option value=\"{$key}\">{$label}</option>";
		}

		$html .= '</select>';
		$html .= '</div>';

		// Submit button
		$html .= '<button type="submit" class="btn btn-primary">Continue</button>';

		$html .= '</form>';

		return $html;
	}

	/**
	 * Generate the form with fields for all placeholders
	 *
	 * @return string HTML form
	 */
	private function generatePlaceholderForm(): string {
		$pageTitle = $this->availablePageTypes[$this->selectedPageType];

		$html = '<form method="post" class="legal-page-form" action="index.php">';
		$html .= '<input type="hidden" name="action" value="legal/generate_page">';
		$html .= '<input type="hidden" name="page_type" value="' . htmlspecialchars($this->selectedPageType) . '">';

		// Form title
		$html .= "<h2>Generate {$pageTitle}</h2>";
		$html .= '<p>Fill in the fields below to customize your legal page.</p>';

		// Group common fields at the top
		$html .= '<h3>Company Information</h3>';
		$html .= '<div class="row">';

		// Common fields that should appear in most templates
		$commonFields = [
			'company_name'    => 'Company Name',
			'company_address' => 'Company Address',
			'company_email'   => 'Contact Email',
			'website_url'     => 'Website URL',
			'website_name'    => 'Website Name'
		];

		foreach ($commonFields as $field => $label) {
			if (array_key_exists($field, $this->placeholders)) {
				$value = htmlspecialchars($this->placeholders[$field]);
				$html .= '<div class="col-md-6 mb-3">';
				$html .= "<label for=\"{$field}\">{$label}</label>";
				$html .= "<input type=\"text\" name=\"{$field}\" id=\"{$field}\" class=\"form-control\" value=\"{$value}\">";
				$html .= '</div>';

				// Remove from placeholders so we don't duplicate
				unset($this->placeholders[$field]);
			}
		}

		$html .= '</div>'; // End row

		// Now add template-specific fields
		if (!empty($this->placeholders)) {
			$html .= '<h3>Page Specific Information</h3>';
			$html .= '<div class="row">';

			foreach ($this->placeholders as $field => $value) {
				$label = ucwords(str_replace('_', ' ', $field));
				$value = htmlspecialchars($value);

				$html .= '<div class="col-md-6 mb-3">';
				$html .= "<label for=\"{$field}\">{$label}</label>";
				$html .= "<input type=\"text\" name=\"{$field}\" id=\"{$field}\" class=\"form-control\" value=\"{$value}\">";
				$html .= '</div>';
			}

			$html .= '</div>'; // End row
		}

		// Submit and cancel buttons
		$html .= '<div class="mt-4">';
		$html .= '<button type="submit" class="btn btn-primary">Generate Page</button>';
		$html .= ' <a href="index.php?action=legal/form" class="btn btn-secondary">Cancel</a>';
		$html .= '</div>';

		$html .= '</form>';

		return $html;
	}

	/**
	 * Process the submitted form data and generate the legal page
	 *
	 * @param array $formData The submitted form data
	 * @return string Generated HTML content or error message
	 */
	public function processForm(array $formData): string {
		$pageType = $formData['page_type'] ?? '';

		// Validate page type
		if (empty($pageType) || !array_key_exists($pageType, $this->availablePageTypes)) {
			return json_encode(['error' => 'Invalid page type']);
		}

		// Set the page type and load placeholders
		$this->setPageType($pageType);

		// Get template content
		$templatePath = $this->getTemplatePath($pageType);
		if (!file_exists($templatePath)) {
			return json_encode(['error' => 'Template not found']);
		}

		$content = file_get_contents($templatePath);

		// Replace placeholders with form data
		foreach ($this->placeholders as $placeholder => $defaultValue) {
			$value = $formData[$placeholder] ?? $defaultValue;
			$content = str_replace('{{' . $placeholder . '}}', $value, $content);
		}

		// Here you would typically save the content to a file or database
		// For now, we'll just return the generated content
		return json_encode([
			'success'  => true,
			'pageType' => $this->availablePageTypes[$pageType],
			'content'  => $content
		]);
	}
}

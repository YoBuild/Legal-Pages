<?php
// src/Yohns/Gens/Legal/LegalPageController.php

namespace Yohns\Gens\Legal;

/**
 * LegalPageController - Unified controller for legal page generation
 *
 * Handles AJAX requests for both multi-step form workflows (init/next_step/previous_step)
 * and direct page generation flows (showForm/selectType/generatePage/deletePage).
 *
 * @package Yohns\Gens\Legal
 */
class LegalPageController {
	/** @var LegalPageForm The form state manager */
	private LegalPageForm $form;

	/** @var LegalPageGenerator The page generator */
	private LegalPageGenerator $generator;

	/** @var LegalPageTemplate The template manager */
	private LegalPageTemplate $template;

	/** @var LegalPageConfig The configuration manager */
	private LegalPageConfig $config;

	/** @var LegalContentPresets The preset manager */
	private LegalContentPresets $presets;

	/** @var string The output directory for generated pages */
	private string $outputDir;

	/**
	 * Constructor - Initializes the controller
	 *
	 * @param string $configDir The configuration directory
	 * @param string $templateDir The template directory
	 * @param string $outputDir The output directory for generated pages
	 */
	public function __construct(
		string $configDir = '',
		string $templateDir = '',
		string $outputDir = ''
	) {
		// Initialize components
		$this->config = new LegalPageConfig($configDir);
		$this->template = new LegalPageTemplate();
		$this->generator = new LegalPageGenerator('', '', [], $templateDir);
		$this->form = new LegalPageForm();
		$this->presets = new LegalContentPresets();

		// Set output directory
		$this->outputDir = $outputDir ?: __DIR__ . '/../../generated/legal';
	}

	/**
	 * Handle AJAX request by routing to appropriate method
	 *
	 * @return array JSON response ready for encoding
	 */
	public function handleRequest(): array {
		$action = $_POST['action'] ?? '';

		// Normalize action (remove any prefixes like 'legal/')
		$action = str_replace('legal/', '', $action);

		// Route to appropriate handler method
		switch ($action) {
			// Multi-step form workflow
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

			// Classic form-based workflow (for compatibility)
			case 'form':
			case 'showForm':
				return $this->showForm();
			case 'select_type':
			case 'selectType':
				return $this->selectType();
			case 'generate_page':
			case 'generatePage':
				return $this->generatePage();
			case 'delete_page':
			case 'deletePage':
				return $this->deletePage();

			default:
				return [
					'success' => false,
					'message' => 'Invalid action: ' . $action
				];
		}
	}

	/**
	 * Initialize the multi-step form
	 *
	 * @return array JSON response
	 */
	private function initForm(): array {
		try {
			$defaultData = $this->config->getDefaultPlaceholders();
			$this->form->setFormData($defaultData);

			return [
				'success'  => true,
				'step'     => 1,
				'formData' => $this->form->getFormData()
			];
		} catch (\Exception $e) {
			return [
				'success' => false,
				'message' => $e->getMessage()
			];
		}
	}

	/**
	 * Move to next step in the form
	 *
	 * @return array JSON response
	 */
	private function nextStep(): array {
		try {
			// Update form data
			$formData = $_POST['formData'] ?? [];
			$this->form->setFormData($formData);

			// Move to next step
			$this->form->nextStep();

			return [
				'success'  => true,
				'step'     => $this->form->getCurrentStep(),
				'formData' => $this->form->getFormData()
			];
		} catch (\Exception $e) {
			return [
				'success' => false,
				'message' => $e->getMessage()
			];
		}
	}

	/**
	 * Move to previous step in the form
	 *
	 * @return array JSON response
	 */
	private function previousStep(): array {
		try {
			// Update form data
			$formData = $_POST['formData'] ?? [];
			$this->form->setFormData($formData);

			// Move to previous step
			$this->form->previousStep();

			return [
				'success'  => true,
				'step'     => $this->form->getCurrentStep(),
				'formData' => $this->form->getFormData()
			];
		} catch (\Exception $e) {
			return [
				'success' => false,
				'message' => $e->getMessage()
			];
		}
	}

	/**
	 * Get website type presets
	 *
	 * @return array JSON response with presets
	 */
	private function getWebsitePresets(): array {
		try {
			$websiteType = $_POST['websiteType'] ?? '';
			$presets = $this->presets->getPresetsForWebsiteType($websiteType);

			return [
				'success' => true,
				'presets' => $presets
			];
		} catch (\Exception $e) {
			return [
				'success' => false,
				'message' => $e->getMessage()
			];
		}
	}

	/**
	 * Determine theme preference for responses.
	 *
	 * Order of detection:
	 * 1. Explicit `theme` POST parameter (light|dark|auto)
	 * 2. Client hint header (Sec-CH-Prefers-Color-Scheme)
	 * 3. Fallback to 'dark'
	 *
	 * @return string One of 'light'|'dark'|'auto'
	 */
	private function determineTheme(): string {
		// 1) explicit POST parameter
		if (!empty($_POST['theme'])) {
			$val = strtolower((string) $_POST['theme']);
			if (in_array($val, ['light', 'dark', 'auto'], true)) {
				return $val;
			}
		}

		// 2) formData may carry theme
		if (!empty($_POST['formData']) && is_array($_POST['formData'])) {
			$formData = $_POST['formData'];
			if (!empty($formData['theme'])) {
				$val = strtolower((string) $formData['theme']);
				if (in_array($val, ['light', 'dark', 'auto'], true)) {
					return $val;
				}
			}
		}

		// 3) client hint header (may be provided by modern browsers / fetch)
		$headers = $_SERVER;
		if (!empty($headers['HTTP_SEC_CH_PREFERS_COLOR_SCHEME'])) {
			$val = strtolower((string) $headers['HTTP_SEC_CH_PREFERS_COLOR_SCHEME']);
			if (in_array($val, ['light', 'dark', 'auto'], true)) {
				return $val;
			}
		}

		// 4) No reliable server-side preference detected — default to dark as requested
		return 'dark';
	}

	/**
	 * Generate preview of the legal page
	 *
	 * @return array JSON response with markdown and HTML preview
	 */
	private function generatePreview(): array {
		try {
			$formData = $_POST['formData'] ?? [];
			$pageType = $formData['pageType'] ?? '';
			$websiteType = $formData['websiteType'] ?? '';

			if (empty($pageType) || empty($websiteType)) {
				return [
					'success' => false,
					'message' => 'Page type and website type are required'
				];
			}

			// Determine requested output format: html|markdown|both
			$outputFormat = strtolower($formData['output_format'] ?? ($_POST['output_format'] ?? 'html'));

			// Determine theme preference (server-side best-effort; client should send explicit theme when possible)
			$theme = $this->determineTheme();

			// Create generator with page and website type
			$generator = new LegalPageGenerator($pageType, $websiteType, $formData);

			// Generate markdown always (templates are markdown by default)
			$markdown = $generator->generate();

			// Convert to HTML when needed
			$html = ($outputFormat === 'markdown') ? '' : $generator->convertToHtml();

			$response = ['success' => true, 'theme' => $theme];

			switch ($outputFormat) {
				case 'markdown':
					$response['markdown'] = $markdown;
					break;
				case 'both':
					$response['markdown'] = $markdown;
					$response['html'] = $html;
					break;
				case 'html':
				default:
					$response['html'] = $html;
					break;
			}

			return $response;
		} catch (\Exception $e) {
			return [
				'success' => false,
				'message' => $e->getMessage()
			];
		}
	}

	/**
	 * Generate final legal page and save
	 *
	 * @return array JSON response
	 */
	private function generateFinal(): array {
		try {
			$formData = $_POST['formData'] ?? [];
			$pageType = $formData['pageType'] ?? '';
			$websiteType = $formData['websiteType'] ?? '';

			if (empty($pageType) || empty($websiteType)) {
				return [
					'success' => false,
					'message' => 'Page type and website type are required'
				];
			}


			// Determine requested output format: html|markdown|both
			$outputFormat = strtolower($formData['output_format'] ?? ($_POST['output_format'] ?? 'both'));

			// Determine theme (server-side best-effort)
			$theme = $this->determineTheme();

			// Create generator
			$generator = new LegalPageGenerator($pageType, $websiteType, $formData);

			// Generate markdown and HTML as needed
			$markdown = ($outputFormat === 'html') ? '' : $generator->generate();
			$html = ($outputFormat === 'markdown') ? '' : $generator->convertToHtml();

			$result = ['success' => true, 'message' => 'Legal page generated successfully!', 'theme' => $theme];

			if ($outputFormat === 'markdown' || $outputFormat === 'both') {
				$filename = $pageType . '.md';
				$generator->savePage($markdown, $filename, $this->outputDir);
				$result['markdown_filename'] = $filename;
			}

			if ($outputFormat === 'html' || $outputFormat === 'both') {
				$htmlFilename = $pageType . '.html';
				$generator->savePage($html, $htmlFilename, $this->outputDir);
				$result['filename'] = $htmlFilename;
			}

			return $result;
		} catch (\Exception $e) {
			return [
				'success' => false,
				'message' => $e->getMessage()
			];
		}
	}

	/**
	 * Show the initial form (classic flow)
	 *
	 * @return array JSON response
	 */
	private function showForm(): array {
		try {
			$templates = $this->generator->getAvailableTemplates();

			return [
				'success'   => true,
				'templates' => $templates
			];
		} catch (\Exception $e) {
			return [
				'success' => false,
				'message' => $e->getMessage()
			];
		}
	}

	/**
	 * Handle page type selection (classic flow)
	 *
	 * @return array JSON response
	 */
	private function selectType(): array {
		try {
			$pageType = $_POST['pageType'] ?? '';
			if (empty($pageType)) {
				return [
					'success' => false,
					'message' => 'Page type is required'
				];
			}

			// Get placeholder info for this page type
			$generator = new LegalPageGenerator($pageType, 'personal');
			$templateFile = $generator->findTemplate();

			if (!$templateFile) {
				return [
					'success' => false,
					'message' => 'Template not found for page type: ' . $pageType
				];
			}

			$content = file_get_contents($templateFile);
			$placeholders = $generator->extractPlaceholders($content);

			return [
				'success'       => true,
				'pageType'      => $pageType,
				'placeholders'  => $placeholders,
				'defaultValues' => $this->config->getDefaultPlaceholders()
			];
		} catch (\Exception $e) {
			return [
				'success' => false,
				'message' => $e->getMessage()
			];
		}
	}

	/**
	 * Generate page (classic flow)
	 *
	 * @return array JSON response
	 */
	private function generatePage(): array {
		try {
			$formData = $_POST['formData'] ?? [];
			$pageType = $formData['pageType'] ?? '';
			$websiteType = $formData['websiteType'] ?? 'personal';

			if (empty($pageType)) {
				return [
					'success' => false,
					'message' => 'Page type is required'
				];
			}


			// Determine requested output format: html|markdown|both
			$outputFormat = strtolower($formData['output_format'] ?? ($_POST['output_format'] ?? 'html'));

			// Determine theme
			$theme = $this->determineTheme();

			// Generate
			$generator = new LegalPageGenerator($pageType, $websiteType, $formData);
			$markdown = ($outputFormat === 'html') ? '' : $generator->generate();
			$html = ($outputFormat === 'markdown') ? '' : $generator->convertToHtml();

			// Save and prepare response
			$result = ['success' => true, 'theme' => $theme];

			if ($outputFormat === 'markdown' || $outputFormat === 'both') {
				$mdFile = $pageType . '.md';
				$generator->savePage($markdown, $mdFile, $this->outputDir);
				$result['markdown_filename'] = $mdFile;
			}

			if ($outputFormat === 'html' || $outputFormat === 'both') {
				$htmlFile = $pageType . '.html';
				$generator->savePage($html, $htmlFile, $this->outputDir);
				$result['filename'] = $htmlFile;
				$result['content'] = $html;
			}

			// Save custom config if enabled
			try {
				$this->config->saveCustomConfig($formData);
			} catch (\Exception $e) {
				// Ignore config save errors
			}

			return $result;
		} catch (\Exception $e) {
			return [
				'success' => false,
				'message' => $e->getMessage()
			];
		}
	}

	/**
	 * Delete a generated page
	 *
	 * @return array JSON response
	 */
	private function deletePage(): array {
		try {
			$filename = $_POST['filename'] ?? '';
			if (empty($filename)) {
				return [
					'success' => false,
					'message' => 'Filename is required'
				];
			}

			$path = rtrim($this->outputDir, '/\\') . '/' . basename($filename);

			if (!file_exists($path)) {
				return [
					'success' => false,
					'message' => 'File does not exist'
				];
			}

			if (!unlink($path)) {
				return [
					'success' => false,
					'message' => 'Failed to delete file'
				];
			}

			return [
				'success' => true,
				'message' => 'Legal page deleted successfully'
			];
		} catch (\Exception $e) {
			return [
				'success' => false,
				'message' => $e->getMessage()
			];
		}
	}
}

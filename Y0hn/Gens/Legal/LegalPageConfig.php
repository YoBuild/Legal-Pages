<?php
// src/Y0hn/Gens/Legal/LegalPageConfig.php

namespace Y0hn\Gens\Legal;

use Yohns\Core\Config;

/**
 * LegalPageConfig - Manages configuration and common values for legal pages
 *
 * Provides access to common company/website information from Yohns\Core\Config
 * and supports saving/loading custom placeholder configurations for legal pages.
 *
 * @package Y0hn\Gens\Legal
 */
class LegalPageConfig {
	/** @var array Common information that can be used across all legal pages */
	private array $commonInfo = [];

	/** @var array Custom configuration values specific to legal pages */
	private array $customConfig = [];

	/** @var string Config file name for custom legal page configuration */
	private const CONFIG_FILE = 'legal.php';

	/**
	 * Constructor - Initializes the configuration manager
	 *
	 * @param string $configDir Optional custom config directory path
	 */
	public function __construct(string $configDir = '') {
		// Initialize Config if needed
		if (!empty($configDir)) {
			try {
				new Config($configDir);
			} catch (\Exception $e) {
				// Config already initialized, ignore
			}
		}

		// Load common information from site config
		$this->loadCommonInfo();

		// Load custom legal page configuration
		$this->loadCustomConfig();
	}

	/**
	 * Load common information from Yohns\Core\Config
	 *
	 * Loads site configuration into colon-format keys (e.g., company:name).
	 *
	 * @return void
	 */
	private function loadCommonInfo(): void {
		try {
			// Load common information from site configuration
			$this->commonInfo = [
				// Company information
				'company:name'       => Config::get('company_name', 'site') ?? '',
				'company:legal_name' => Config::get('company_legal_name', 'site') ?? '',
				'company:address'    => Config::get('company_address', 'site') ?? '',
				'company:city'       => Config::get('company_city', 'site') ?? '',
				'company:state'      => Config::get('company_state', 'site') ?? '',
				'company:zip'        => Config::get('company_zip', 'site') ?? '',
				'company:country'    => Config::get('company_country', 'site') ?? 'United States',
				'company:phone'      => Config::get('company_phone', 'site') ?? '',
				'company:email'      => Config::get('contact_email', 'site') ?? '',

				// Website information
				'website:url'        => Config::get('site_url', 'site') ?? '',
				'website:name'       => Config::get('site_name', 'site') ?? '',

				// Dates and times
				'current:year'       => date('Y'),
				'current:date'       => date('F j, Y'),
			];
		} catch (\Exception $e) {
			// Config not available, use defaults
			$this->commonInfo = [
				'company:country' => 'United States',
				'current:year'    => date('Y'),
				'current:date'    => date('F j, Y'),
			];
		}
	}

	/**
	 * Load custom legal page configuration
	 *
	 * @return void
	 */
	private function loadCustomConfig(): void {
		try {
			// Try to load custom legal configuration
			$this->customConfig = Config::getAll('legal') ?? [];
		} catch (\Exception $e) {
			// Config not available or legal config doesn't exist
			$this->customConfig = [];
		}
	}

	/**
	 * Get the default placeholders for legal page generation
	 *
	 * Returns combined commonInfo and customConfig in colon-format keys.
	 *
	 * @return array Associative array of placeholder keys and values
	 */
	public function getDefaultPlaceholders(): array {
		return array_merge($this->commonInfo, $this->customConfig);
	}

	/**
	 * Get a common information value
	 *
	 * @param string $key The key to get (e.g., 'company:name' or 'company_name')
	 * @param mixed $default Default value if key doesn't exist
	 * @return mixed The value or default if not found
	 */
	public function getCommonInfo(string $key, $default = null) {
		// Normalize underscore to colon format
		$key = str_replace('_', ':', $key);
		return $this->commonInfo[$key] ?? $default;
	}

	/**
	 * Get all common information
	 *
	 * @return array All common information in colon-format keys
	 */
	public function getAllCommonInfo(): array {
		return $this->commonInfo;
	}

	/**
	 * Get a custom configuration value
	 *
	 * @param string $key The key to get
	 * @param mixed $default Default value if key doesn't exist
	 * @return mixed The value or default if not found
	 */
	public function getCustomConfig(string $key, $default = null) {
		return $this->customConfig[$key] ?? $default;
	}

	/**
	 * Get all custom configuration
	 *
	 * @return array All custom configuration
	 */
	public function getAllCustomConfig(): array {
		return $this->customConfig;
	}

	/**
	 * Save custom configuration to the config file
	 *
	 * @param array $config The configuration to save
	 * @return bool True if saved successfully
	 * @throws \RuntimeException If config directory is not available
	 */
	public function saveCustomConfig(array $config): bool {
		// Merge new config with existing
		$this->customConfig = array_merge($this->customConfig, $config);

		try {
			// Try to save to config using Config class
			// If Config has a way to save, use it; otherwise try default location
			Config::set('legal', $this->customConfig);
			return true;
		} catch (\Exception $e) {
			// Fallback: try to write directly to a config file
			$defaultConfigFile = __DIR__ . '/../../config/legal.php';
			$configDir = dirname($defaultConfigFile);

			if (!is_dir($configDir)) {
				mkdir($configDir, 0755, true);
			}

			$content = "<?php\nreturn " . var_export($this->customConfig, true) . ";\n";
			if (file_put_contents($defaultConfigFile, $content) === false) {
				throw new \RuntimeException("Failed to save custom config to {$defaultConfigFile}");
			}

			return true;
		}
	}	/**
		 * Get a complete array of placeholder values
		 *
		 * Combines common info and custom config.
		 *
		 * @return array Combined placeholder values
		 */
	public function getPlaceholderValues(): array {
		return array_merge($this->commonInfo, $this->customConfig);
	}
}

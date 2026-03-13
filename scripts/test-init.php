<?php
/**
 * Test script to verify the init AJAX action returns config defaults
 *
 * Run: php scripts/test-init.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Yohns\Gens\Legal\LegalPageController;

echo "=== Testing LegalPageController init action ===\n\n";

try {
	// Simulate an AJAX request
	$_POST['action'] = 'init';

	$configDir = __DIR__ . '/../config';
	$templateDir = __DIR__ . '/../legal';
	$outputDir = __DIR__ . '/../generated/legal';

	$controller = new LegalPageController($configDir, $templateDir, $outputDir);
	$response = $controller->handleRequest();

	echo "Response from init action:\n";
	echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

	if ($response['success']) {
		echo "✓ Init action succeeded\n";
		if (!empty($response['formData'])) {
			echo "✓ Form data populated:\n";
			foreach (array_slice($response['formData'], 0, 5) as $key => $value) {
				echo "  - $key: " . (is_array($value) ? '[array]' : $value) . "\n";
			}
			echo "  ... and " . (count($response['formData']) - 5) . " more fields\n";
		}
	} else {
		echo "✗ Init action failed: " . $response['message'] . "\n";
	}
} catch (\Exception $e) {
	echo "✗ Error: " . $e->getMessage() . "\n";
	echo $e->getTraceAsString() . "\n";
}

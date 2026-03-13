<?php
// AJAX handler for legal page generation - routes to unified Yohns\Gens\Legal controller

// Require autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Import the merged Legal controller
use Yohns\Gens\Legal\LegalPageController;

// Set header for JSON responses
header('Content-Type: application/json');

// Set default error response
$response = ['success' => false, 'message' => 'Invalid request'];

// Check if POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	try {
		// Initialize the controller
		// The controller will look for templates in legal/ and output to generated/legal/
		$configDir = __DIR__ . '/../config';
		$templateDir = __DIR__ . '/../legal';  // Where markdown templates live
		$outputDir = __DIR__ . '/../generated/legal';  // Where to save generated pages

		$controller = new LegalPageController($configDir, $templateDir, $outputDir);

		// Process the request and get array response
		// The controller's handleRequest() method routes based on the 'action' POST field
		$response = $controller->handleRequest();

	} catch (\Exception $e) {
		$response = [
			'success' => false,
			'message' => 'Error: ' . $e->getMessage()
		];
	}
}

// Output JSON response
echo json_encode($response);

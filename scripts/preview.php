<?php
/**
 * Legal Page Generator Preview Script
 *
 * Quick script to test the merged LegalPageGenerator with actual templates and placeholders.
 * Usage: php scripts/preview.php --page-type=privacy-policy --website-type=personal
 *
 * Run from project root:
 *   php -S localhost:8000 -t .
 *   Then: http://localhost:8000/scripts/preview.php?pageType=privacy-policy&websiteType=personal
 */

// Require Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

use Yohns\Gens\Legal\LegalPageGenerator;
use Yohns\Gens\Legal\LegalPageConfig;

// Get parameters from query string or command line
$pageType = $_GET['pageType'] ?? null;
$websiteType = $_GET['websiteType'] ?? null;
$format = $_GET['format'] ?? 'html'; // html, markdown, or preview

// Parse command-line arguments if running from CLI
if (php_sapi_name() === 'cli' && isset($argv)) {
	foreach ($argv as $arg) {
		if (strpos($arg, '--page-type=') === 0) {
			$pageType = substr($arg, strlen('--page-type='));
		} elseif (strpos($arg, '--website-type=') === 0) {
			$websiteType = substr($arg, strlen('--website-type='));
		} elseif (strpos($arg, '--format=') === 0) {
			$format = substr($arg, strlen('--format='));
		}
	}
}

// Set defaults
$pageType = $pageType ?? 'privacy-policy';
$websiteType = $websiteType ?? 'personal';

// Sanitize input
$pageType = preg_replace('/[^a-z\-]/', '', $pageType);
$websiteType = preg_replace('/[^a-z\-]/', '', $websiteType);
$format = preg_replace('/[^a-z]/', '', $format);

try {
	// Load config
	$config = new LegalPageConfig();
	$defaultPlaceholders = $config->getDefaultPlaceholders();

	// Create generator
	$generator = new LegalPageGenerator($pageType, $websiteType, $defaultPlaceholders);

	// Check if template exists
	$template = $generator->findTemplate();
	if (!$template) {
		die("Error: Template not found for '{$pageType}' ({$websiteType})\n");
	}

	echo "Generating {$pageType} for {$websiteType}...\n";
	echo "Template: {$template}\n\n";

	// Generate
	$markdown = $generator->generate();
	$html = $generator->convertToHtml();

	// Output based on format
	if (php_sapi_name() === 'cli') {
		// CLI output
		if ($format === 'markdown') {
			echo "=== MARKDOWN ===\n";
			echo $markdown;
		} elseif ($format === 'preview') {
			echo "=== PREVIEW (first 500 chars of HTML) ===\n";
			echo substr($html, 0, 500) . "...\n";
		} else {
			echo "=== HTML (first 500 chars) ===\n";
			echo substr($html, 0, 500) . "...\n";
		}
	} else {
		// Web output
		header('Content-Type: text/html; charset=utf-8');
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<title>Legal Page Preview - <?php echo ucwords(str_replace('-', ' ', $pageType)); ?></title>
			<style>
				body {max-width: 900px;margin: 0 auto;padding: 20px; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;}
				.controls {background: #f0f0f0;padding: 15px;border-radius: 5px;margin-bottom: 20px;}
				.controls input,.controls select,.controls button {padding: 8px;font-size: 14px;}
				.preview {border: 1px solid #ddd;padding: 20px;border-radius: 5px;}
				h1 {color: #333;}
				.meta {color: #666;font-size: 13px;margin-bottom: 20px;}
			</style>
		</head>
		<body>
			<h1>Legal Page Preview</h1>
			<div class="controls">
				<form method="get" style="display: flex; gap: 10px; flex-wrap: wrap;">
					<div>
						<label for="pageType">Page Type:</label>
						<input type="text" name="pageType" id="pageType" value="<?php echo htmlspecialchars($pageType); ?>"
							placeholder="privacy-policy">
					</div>
					<div>
						<label for="websiteType">Website Type:</label>
						<select name="websiteType" id="websiteType">
							<option value="personal" <?php echo $websiteType === 'personal' ? 'selected' : ''; ?>>Personal</option>
							<option value="ecommerce" <?php echo $websiteType === 'ecommerce' ? 'selected' : ''; ?>>E-commerce</option>
							<option value="social" <?php echo $websiteType === 'social' ? 'selected' : ''; ?>>Social</option>
						</select>
					</div>
					<div>
						<label for="format">Format:</label>
						<select name="format" id="format">
							<option value="html" <?php echo $format === 'html' ? 'selected' : ''; ?>>HTML</option>
							<option value="markdown" <?php echo $format === 'markdown' ? 'selected' : ''; ?>>Markdown</option>
							<option value="preview" <?php echo $format === 'preview' ? 'selected' : ''; ?>>Preview</option>
						</select>
					</div>
					<button type="submit">Generate</button>
				</form>
			</div>
			<div class="meta">
				<strong>Template:</strong> <?php echo htmlspecialchars($template); ?><br>
				<strong>Page Type:</strong> <?php echo htmlspecialchars($pageType); ?><br>
				<strong>Website Type:</strong> <?php echo htmlspecialchars($websiteType); ?>
			</div>
			<?php if ($format === 'markdown'): ?>
				<div class="preview">
					<h2>Markdown Output</h2>
					<pre><?php echo htmlspecialchars($markdown); ?></pre>
				</div>
			<?php elseif ($format === 'preview'): ?>
				<div class="preview">
					<h2>Preview (first 1000 chars)</h2>
					<pre><?php echo htmlspecialchars(substr($html, 0, 1000)); ?></pre>
				</div>
			<?php else: ?>
				<div class="preview">
					<h2>HTML Rendered</h2>
					<?php echo $html; ?>
				</div>
			<?php endif; ?>
		</body>
		</html>
		<?php
	}

} catch (\Exception $e) {
	header('Content-Type: text/plain');
	http_response_code(500);
	echo "Error: " . $e->getMessage() . "\n";
	echo $e->getTraceAsString();
}

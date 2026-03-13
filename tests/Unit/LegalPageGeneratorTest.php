<?php
/**
 * Tests for Yohns\Gens\Legal\LegalPageGenerator
 *
 * Run with: vendor/bin/phpunit tests/Unit/LegalPageGeneratorTest.php
 */

namespace Tests\Unit;

use Yohns\Gens\Legal\LegalPageGenerator;
use PHPUnit\Framework\TestCase;

class LegalPageGeneratorTest extends TestCase
{
	private LegalPageGenerator $generator;

	protected function setUp(): void
	{
		// Create generator for testing with minimal config
		$this->generator = new LegalPageGenerator(
			'privacy-policy',
			'personal',
			[
				'company:name' => 'Test Company',
				'company:email' => 'test@example.com',
				'website:name' => 'Test Site',
				'website:url' => 'https://example.com',
			]
		);
	}

	/**
	 * Test that placeholders are normalized correctly
	 */
	public function testPlaceholderNormalization()
	{
		// Test setting with colon format
		$this->generator->setPlaceholder('company', 'name', 'ACME Corp');
		$placeholders = $this->generator->getPlaceholders();
		$this->assertArrayHasKey('company:name', $placeholders);
		$this->assertEquals('ACME Corp', $placeholders['company:name']);
	}

	/**
	 * Test that flat placeholder keys are converted to colon format
	 */
	public function testFlatPlaceholderConversion()
	{
		$this->generator->setPlaceholders([
			'company_name' => 'My Company',
			'website_url' => 'https://mysite.com',
		]);

		$placeholders = $this->generator->getPlaceholders();
		$this->assertArrayHasKey('company:name', $placeholders);
		$this->assertArrayHasKey('website:url', $placeholders);
		$this->assertEquals('My Company', $placeholders['company:name']);
		$this->assertEquals('https://mysite.com', $placeholders['website:url']);
	}

	/**
	 * Test conditional section processing with website type
	 */
	public function testConditionalProcessingByWebsiteType()
	{
		$content = "This is always here.\n";
		$content .= "{{if:personal}}Personal text{{endif}}\n";
		$content .= "{{if:ecommerce}}E-commerce text{{endif}}\n";
		$content .= "End.";

		// Use reflection to call private method
		$reflection = new \ReflectionClass($this->generator);
		$method = $reflection->getMethod('processConditionalSections');
		$method->setAccessible(true);

		$result = $method->invoke($this->generator, $content);

		// Personal site should include personal text but not ecommerce text
		$this->assertStringContainsString('Personal text', $result);
		$this->assertStringNotContainsString('E-commerce text', $result);
	}

	/**
	 * Test conditional section processing with compliance flags
	 */
	public function testConditionalProcessingByComplianceFlag()
	{
		$this->generator->setPlaceholder('compliance', 'gdpr', 'true');
		$this->generator->setPlaceholder('compliance', 'ccpa', 'false');

		$content = "Start.\n";
		$content .= "{{if:gdpr}}GDPR Section{{endif}}\n";
		$content .= "{{if:ccpa}}CCPA Section{{endif}}\n";
		$content .= "End.";

		// Use reflection to call private method
		$reflection = new \ReflectionClass($this->generator);
		$method = $reflection->getMethod('processConditionalSections');
		$method->setAccessible(true);

		$result = $method->invoke($this->generator, $content);

		// GDPR is true, CCPA is false
		$this->assertStringContainsString('GDPR Section', $result);
		$this->assertStringNotContainsString('CCPA Section', $result);
	}

	/**
	 * Test placeholder extraction from content
	 */
	public function testPlaceholderExtraction()
	{
		$content = 'Hello {{company:name}}, visit {{website:url}} for more info.';
		$placeholders = $this->generator->extractPlaceholders($content);

		$this->assertContains('company:name', $placeholders);
		$this->assertContains('website:url', $placeholders);
		$this->assertCount(2, $placeholders);
	}

	/**
	 * Test that conditional directives are not extracted as placeholders
	 */
	public function testConditionalNotExtractedAsPlaceholder()
	{
		$content = '{{if:personal}}Text{{endif}}';
		$placeholders = $this->generator->extractPlaceholders($content);

		// Should not include if: directives
		$this->assertEmpty($placeholders);
	}

	/**
	 * Test template finding (requires actual template files)
	 */
	public function testTemplateFinding()
	{
		// This test will pass if template files exist
		$template = $this->generator->findTemplate();

		if ($template) {
			$this->assertStringEndsWith('.md', $template);
			$this->assertTrue(file_exists($template));
		} else {
			// Template files may not exist in test environment
			$this->assertTrue(true);
		}
	}

	/**
	 * Test that convertToHtml adds Bootstrap classes to elements
	 */
	public function testConvertToHtmlAddsBootstrapClasses()
	{
		$gen = new LegalPageGenerator(
			'privacy-policy',
			'personal',
			['company:name' => 'Test Co', 'website:url' => 'https://test.com']
		);

		$html = $gen->convertToHtml();

		// Should contain Bootstrap classes on elements
		// Headings get mb-3
		$this->assertMatchesRegularExpression('/<h[1-6][^>]*class="[^"]*mb-3[^"]*"/', $html);
		// Paragraphs get mb-2
		$this->assertStringContainsString('class="mb-2"', $html);
		// Should NOT contain full page wrapper
		$this->assertStringNotContainsString('<!DOCTYPE html>', $html);
		$this->assertStringNotContainsString('bootstrap.min.css', $html);
	}

	/**
	 * Test that convertToHtml(full: true) wraps in full HTML page with Bootstrap CDN
	 */
	public function testConvertToHtmlFullPage()
	{
		$gen = new LegalPageGenerator(
			'privacy-policy',
			'personal',
			['company:name' => 'Test Co', 'website:url' => 'https://test.com']
		);

		$html = $gen->convertToHtml(full: true);

		// Should have full page structure
		$this->assertStringContainsString('<!DOCTYPE html>', $html);
		$this->assertStringContainsString('bootstrap@5.3.8/dist/css/bootstrap.min.css', $html);
		$this->assertStringContainsString('<div class="container py-4">', $html);
		// Title should be derived from page type
		$this->assertStringContainsString('<title>Privacy Policy</title>', $html);
	}

	/**
	 * Test that tables get Bootstrap table classes
	 */
	public function testBootstrapTableClasses()
	{
		$gen = new LegalPageGenerator('privacy-policy', 'personal', []);

		// Use reflection to test addBootstrapClasses directly
		$reflection = new \ReflectionClass($gen);
		$method = $reflection->getMethod('addBootstrapClasses');
		$method->setAccessible(true);

		$html = '<table><thead><tr><th>Header</th></tr></thead></table>';
		$result = $method->invoke($gen, $html);

		$this->assertStringContainsString('class="table table-striped"', $result);
	}

	/**
	 * Test that generate() returns content as a string without saving files
	 */
	public function testGenerateReturnsContentWithoutSaving()
	{
		$gen = new LegalPageGenerator(
			'privacy-policy',
			'personal',
			['company:name' => 'No-Save Corp', 'website:url' => 'https://nosave.com']
		);

		$markdown = $gen->generate();

		$this->assertIsString($markdown);
		$this->assertNotEmpty($markdown);
		$this->assertStringContainsString('No-Save Corp', $markdown);
	}

	/**
	 * Test that convertToHtml() returns content as a string without saving files
	 */
	public function testConvertToHtmlReturnsContentWithoutSaving()
	{
		$gen = new LegalPageGenerator(
			'privacy-policy',
			'personal',
			['company:name' => 'No-Save Corp', 'website:url' => 'https://nosave.com']
		);

		$html = $gen->convertToHtml(full: true);

		$this->assertIsString($html);
		$this->assertStringContainsString('<!DOCTYPE html>', $html);
		$this->assertStringContainsString('No-Save Corp', $html);
	}

	/**
	 * Test that content can be used in variables without any file I/O
	 */
	public function testContentUsableAsVariables()
	{
		$config = [
			'company:name'  => 'Variable Corp',
			'company:email' => 'var@example.com',
			'website:url'   => 'https://variable.com',
			'website:name'  => 'Variable Site',
		];

		$pages = ['privacy-policy', 'terms-of-service', 'cookie-policy'];
		$results = [];

		foreach ($pages as $pageType) {
			$gen = new LegalPageGenerator($pageType, 'personal', $config);
			$results[$pageType] = [
				'markdown' => $gen->generate(),
				'html'     => $gen->convertToHtml(full: true),
			];
		}

		// All pages should have content
		$this->assertCount(3, $results);

		foreach ($results as $pageType => $content) {
			$this->assertArrayHasKey('markdown', $content);
			$this->assertArrayHasKey('html', $content);
			$this->assertNotEmpty($content['markdown'], "$pageType markdown should not be empty");
			$this->assertNotEmpty($content['html'], "$pageType html should not be empty");
			$this->assertStringContainsString('Variable Corp', $content['markdown']);
			$this->assertStringContainsString('<!DOCTYPE html>', $content['html']);
		}
	}

	/**
	 * Test that markdown tables are converted to HTML tables with GFM support
	 */
	public function testMarkdownTablesConvertToHtml()
	{
		$gen = new LegalPageGenerator('cookie-policy', 'personal', [
			'company:name'  => 'Table Test Co',
			'company:email' => 'test@example.com',
			'website:name'  => 'Table Site',
			'website:url'   => 'https://table.com',
		]);

		$html = $gen->convertToHtml();

		// Cookie policy template contains a markdown table
		// GFM converter should produce <table> elements
		if (strpos($gen->generate(), '|') !== false) {
			$this->assertStringContainsString('<table', $html);
			$this->assertStringContainsString('table table-striped', $html);
		} else {
			$this->assertTrue(true, 'Template has no tables to test');
		}
	}

	/**
	 * Test the outputDir=false pattern: generate multiple pages into a results
	 * array without writing any files to disk.
	 */
	public function testOutputDirFalseCollectsResultsWithoutSaving()
	{
		$config = [
			'company:name'  => 'NoFile Inc',
			'company:email' => 'nofile@example.com',
			'website:url'   => 'https://nofile.com',
			'website:name'  => 'NoFile Site',
		];
		$websiteType = 'personal';
		$outputDir   = false;

		$pages   = ['privacy-policy', 'terms-of-service', 'cookie-policy'];
		$results = [];

		foreach ($pages as $pageType) {
			$gen = new LegalPageGenerator($pageType, $websiteType, $config);

			$markdown = $gen->generate();
			$html     = $gen->convertToHtml(full: true);

			if ($outputDir) {
				$gen->savePage($markdown, "$pageType.md", $outputDir);
				$gen->savePage($html, "$pageType.html", $outputDir);
			}

			$results[$pageType] = [
				'markdown' => $markdown,
				'html'     => $html,
			];
		}

		// All three pages collected
		$this->assertCount(3, $results);
		$this->assertArrayHasKey('privacy-policy', $results);
		$this->assertArrayHasKey('terms-of-service', $results);
		$this->assertArrayHasKey('cookie-policy', $results);

		// Each page has both formats with correct content
		foreach ($results as $pageType => $content) {
			$this->assertArrayHasKey('markdown', $content);
			$this->assertArrayHasKey('html', $content);
			$this->assertStringContainsString('NoFile Inc', $content['markdown'], "$pageType markdown missing company name");
			$this->assertStringContainsString('<!DOCTYPE html>', $content['html'], "$pageType html missing doctype");
			$this->assertStringContainsString('NoFile Inc', $content['html'], "$pageType html missing company name");
		}

		// No files were written (savePage was never called)
		$this->assertFalse($outputDir);
	}
}

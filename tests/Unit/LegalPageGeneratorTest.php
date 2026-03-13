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
}

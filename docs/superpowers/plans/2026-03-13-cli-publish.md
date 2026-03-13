# CLI Publish Command — Implementation Plan

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a `php vendor/bin/legal-pages publish` CLI command that copies starter files (config, quickstart script, UI) into the user's project.

**Architecture:** A composer bin script (`bin/legal-pages`) parses CLI args and delegates to `PublishCommand`, which copies files from the package to the user's project, adjusting paths where needed (autoloader, AJAX URLs).

**Tech Stack:** PHP 8.1+, Composer bin scripts, PHPUnit

**Spec:** `docs/superpowers/specs/2026-03-13-cli-publish-design.md`

---

## File Map

| File | Role | Action |
|------|------|--------|
| `bin/legal-pages` | CLI entry point, parses args, calls PublishCommand | Create |
| `Yohns/Gens/Legal/Console/PublishCommand.php` | Copy logic, dir creation, path rewriting | Create |
| `stubs/generate.php` | Quickstart template the user edits and runs | Create |
| `composer.json` | Add `"bin": ["bin/legal-pages"]` | Modify |
| `tests/Unit/PublishCommandTest.php` | Unit tests for PublishCommand | Create |
| `README.md` | Add Getting Started section at top | Modify |

---

## Chunk 1: Core Implementation

### Task 1: Create the generate.php stub

**Files:**
- Create: `stubs/generate.php`

- [ ] **Step 1: Create stubs directory and generate.php**

Create `stubs/generate.php` with this exact content:

```php
<?php
/**
 * Legal Page Generator — Quickstart
 *
 * Edit the $config array below with your site details, then run:
 *   php generate.php
 *
 * Generated files will be saved to the ./generated/ directory.
 */

require __DIR__ . '/../vendor/autoload.php';

use Yohns\Gens\Legal\LegalPageGenerator;

// ── Edit these values ─────────────────────────────────────────────
$websiteType = 'personal'; // 'personal', 'ecommerce', or 'social'

$config = [
    'company' => [
        'name'    => 'Your Company Name',
        'email'   => 'contact@example.com',
        'phone'   => '+1-555-0100',
        'address' => '123 Main Street',
        'country' => 'United States',
    ],
    'website' => [
        'url'  => 'https://example.com',
        'name' => 'Your Site Name',
    ],
];
// ── End config ────────────────────────────────────────────────────

$outputDir = __DIR__ . '/generated';

$pages = ['privacy-policy', 'terms-of-service', 'cookie-policy'];

foreach ($pages as $pageType) {
    $gen = new LegalPageGenerator($pageType, $websiteType, $config);

    // Save Markdown
    $gen->savePage($gen->generate(), "$pageType.md", $outputDir);

    // Save Bootstrap HTML
    $gen->savePage($gen->convertToHtml(full: true), "$pageType.html", $outputDir);

    echo "  [OK] $pageType.md + $pageType.html\n";
}

echo "\nDone! Files saved to: $outputDir/\n";
```

- [ ] **Step 2: Commit**

```bash
git add stubs/generate.php
git commit -m "feat: add generate.php quickstart stub"
```

---

### Task 2: Create PublishCommand class

**Files:**
- Create: `Yohns/Gens/Legal/Console/PublishCommand.php`

- [ ] **Step 1: Create the Console directory and PublishCommand.php**

Create `Yohns/Gens/Legal/Console/PublishCommand.php`:

```php
<?php

namespace Yohns\Gens\Legal\Console;

class PublishCommand
{
    private string $packageRoot;
    private string $projectRoot;
    private string $outputDir;
    private bool $publishConfig = false;
    private bool $publishQuickstart = false;
    private bool $publishUi = false;
    private int $copiedCount = 0;
    private int $skippedCount = 0;

    public function __construct(string $packageRoot, string $projectRoot)
    {
        $this->packageRoot = rtrim($packageRoot, '/\\');
        $this->projectRoot = rtrim($projectRoot, '/\\');
    }

    /**
     * Parse CLI arguments and run the publish command.
     *
     * @param array $argv CLI arguments (from $argv)
     * @return int Exit code (0 = success, 1 = error)
     */
    public function run(array $argv): int
    {
        $this->parseArgs($argv);

        echo "Publishing legal-pages files to: {$this->outputDir}/\n\n";

        try {
            if ($this->publishConfig) {
                $this->copyFile(
                    'config/legal-pages.php',
                    'config/legal-pages.php'
                );
            }

            if ($this->publishQuickstart) {
                $this->copyFile(
                    'stubs/generate.php',
                    'generate.php'
                );
            }

            if ($this->publishUi) {
                $this->copyFile(
                    'legal-generator.html',
                    'legal-generator.html'
                );
                $this->copyFileWithTransform(
                    'js/legal-generator.js',
                    'js/legal-generator.js',
                    function (string $content): string {
                        // Rewrite absolute AJAX paths to relative
                        return str_replace(
                            '/ajax/ajax_legal_handler.php',
                            'ajax/ajax_legal_handler.php',
                            $content
                        );
                    }
                );
                $this->copyFileWithTransform(
                    'ajax/ajax_legal_handler.php',
                    'ajax/ajax_legal_handler.php',
                    function (string $content): string {
                        // Adjust autoloader path for published location
                        return str_replace(
                            "__DIR__ . '/../vendor/autoload.php'",
                            "__DIR__ . '/../../vendor/autoload.php'",
                            $content
                        );
                    }
                );
            }
        } catch (\RuntimeException $e) {
            echo "  [ERROR] {$e->getMessage()}\n";
            return 1;
        }

        echo "\n";
        if ($this->copiedCount > 0) {
            echo "Done! Edit {$this->outputDir}/config/legal-pages.php with your site details, then run:\n";
            echo "  php {$this->outputDir}/generate.php\n";
        }
        if ($this->skippedCount > 0) {
            echo "({$this->skippedCount} file(s) skipped — already exist)\n";
        }

        return 0;
    }

    private function parseArgs(array $argv): void
    {
        $hasFlags = false;
        $this->outputDir = $this->projectRoot . '/legal-pages';

        foreach ($argv as $arg) {
            if ($arg === '--config') {
                $this->publishConfig = true;
                $hasFlags = true;
            } elseif ($arg === '--quickstart') {
                $this->publishQuickstart = true;
                $hasFlags = true;
            } elseif ($arg === '--ui') {
                $this->publishUi = true;
                $hasFlags = true;
            } elseif (str_starts_with($arg, '--dir=')) {
                $dir = substr($arg, 6);
                // Resolve relative to project root
                if (!str_starts_with($dir, '/') && !preg_match('/^[A-Z]:/i', $dir)) {
                    $this->outputDir = $this->projectRoot . '/' . $dir;
                } else {
                    $this->outputDir = $dir;
                }
            }
        }

        // No flags = publish everything
        if (!$hasFlags) {
            $this->publishConfig = true;
            $this->publishQuickstart = true;
            $this->publishUi = true;
        }
    }

    private function copyFile(string $source, string $dest): void
    {
        $this->copyFileWithTransform($source, $dest, null);
    }

    private function copyFileWithTransform(string $source, string $dest, ?callable $transform): void
    {
        $srcPath = $this->packageRoot . '/' . $source;
        $destPath = $this->outputDir . '/' . $dest;

        if (!file_exists($srcPath)) {
            throw new \RuntimeException("Source file not found: {$source}");
        }

        if (file_exists($destPath)) {
            echo "  [SKIP] {$dest} (already exists)\n";
            $this->skippedCount++;
            return;
        }

        $destDir = dirname($destPath);
        if (!is_dir($destDir)) {
            if (!mkdir($destDir, 0755, true)) {
                throw new \RuntimeException("Cannot create directory: {$destDir}");
            }
        }

        $content = file_get_contents($srcPath);
        if ($transform !== null) {
            $content = $transform($content);
        }

        if (file_put_contents($destPath, $content) === false) {
            throw new \RuntimeException("Cannot write file: {$destPath}");
        }

        echo "  [OK] {$dest}\n";
        $this->copiedCount++;
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add Yohns/Gens/Legal/Console/PublishCommand.php
git commit -m "feat: add PublishCommand class for CLI publish"
```

---

### Task 3: Create the bin/legal-pages entry point

**Files:**
- Create: `bin/legal-pages`
- Modify: `composer.json`

- [ ] **Step 1: Create bin/legal-pages**

Create `bin/legal-pages` (no `.php` extension — this is a composer bin script):

```php
#!/usr/bin/env php
<?php
/**
 * CLI entry point for yohns/legal-pages
 *
 * Usage: php vendor/bin/legal-pages publish [--config] [--quickstart] [--ui] [--dir=PATH]
 */

// Find the composer autoloader
// When installed as a dependency: vendor/yohns/legal-pages/bin/legal-pages
// When running from package root: bin/legal-pages
$autoloadPaths = [
    __DIR__ . '/../vendor/autoload.php',       // Running from package root
    __DIR__ . '/../../../autoload.php',         // Installed as dependency
];

$autoloaded = false;
foreach ($autoloadPaths as $path) {
    if (file_exists($path)) {
        require $path;
        $autoloaded = true;
        break;
    }
}

if (!$autoloaded) {
    fwrite(STDERR, "Error: Cannot find composer autoloader. Run 'composer install' first.\n");
    exit(1);
}

use Yohns\Gens\Legal\Console\PublishCommand;

// Determine package root (where templates/config/stubs live)
$packageRoot = dirname(__DIR__);

// Determine project root (where the user's project is)
// When installed as dependency: vendor/yohns/legal-pages/bin -> go up 4 levels
// When running from package root: bin/ -> go up 1 level
if (file_exists(__DIR__ . '/../../../autoload.php')) {
    $projectRoot = dirname(__DIR__, 4); // vendor/yohns/legal-pages/bin -> project root
} else {
    $projectRoot = dirname(__DIR__); // bin/ -> package root (dev mode)
}

// Check for 'publish' subcommand
$args = array_slice($argv, 1);
$command = $args[0] ?? '';

if ($command === 'publish') {
    $cmd = new PublishCommand($packageRoot, $projectRoot);
    exit($cmd->run(array_slice($args, 1)));
}

// Show usage
echo "Usage: php vendor/bin/legal-pages <command>\n\n";
echo "Commands:\n";
echo "  publish    Copy starter files into your project\n\n";
echo "Options for publish:\n";
echo "  --config      Copy config/legal-pages.php\n";
echo "  --quickstart  Copy generate.php quickstart script\n";
echo "  --ui          Copy web UI (HTML, JS, AJAX handler)\n";
echo "  --dir=PATH    Output directory (default: legal-pages/)\n\n";
echo "  No flags = publish everything\n";
exit(0);
```

- [ ] **Step 2: Add bin entry to composer.json**

In `composer.json`, add the `"bin"` key after `"autoload"`:

```json
"bin": ["bin/legal-pages"],
```

- [ ] **Step 3: Test the CLI manually**

Run from project root:

```bash
php bin/legal-pages
```

Expected: Shows usage text with commands and options.

```bash
php bin/legal-pages publish --dir=test-output
```

Expected: Publishes all files to `test-output/` directory. Verify files exist:

```bash
ls test-output/
ls test-output/config/
ls test-output/js/
ls test-output/ajax/
```

Clean up:

```bash
rm -rf test-output/
```

- [ ] **Step 4: Commit**

```bash
git add bin/legal-pages composer.json
git commit -m "feat: add bin/legal-pages CLI entry point with publish command"
```

---

### Task 4: Write tests for PublishCommand

**Files:**
- Create: `tests/Unit/PublishCommandTest.php`

- [ ] **Step 1: Create PublishCommandTest.php**

```php
<?php

namespace Tests\Unit;

use Yohns\Gens\Legal\Console\PublishCommand;
use PHPUnit\Framework\TestCase;

class PublishCommandTest extends TestCase
{
    private string $testOutputDir;
    private string $packageRoot;

    protected function setUp(): void
    {
        $this->packageRoot = dirname(__DIR__, 2);
        $this->testOutputDir = sys_get_temp_dir() . '/legal-pages-test-' . uniqid();
    }

    protected function tearDown(): void
    {
        // Clean up test output directory
        if (is_dir($this->testOutputDir)) {
            $this->removeDir($this->testOutputDir);
        }
    }

    private function removeDir(string $dir): void
    {
        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . '/' . $item;
            is_dir($path) ? $this->removeDir($path) : unlink($path);
        }
        rmdir($dir);
    }

    public function testPublishAllCreatesAllFiles()
    {
        $cmd = new PublishCommand($this->packageRoot, $this->testOutputDir);

        ob_start();
        $exitCode = $cmd->run(['--dir=' . $this->testOutputDir . '/out']);
        ob_end_clean();

        $this->assertEquals(0, $exitCode);
        $this->assertFileExists($this->testOutputDir . '/out/config/legal-pages.php');
        $this->assertFileExists($this->testOutputDir . '/out/generate.php');
        $this->assertFileExists($this->testOutputDir . '/out/legal-generator.html');
        $this->assertFileExists($this->testOutputDir . '/out/js/legal-generator.js');
        $this->assertFileExists($this->testOutputDir . '/out/ajax/ajax_legal_handler.php');
    }

    public function testPublishConfigOnly()
    {
        $cmd = new PublishCommand($this->packageRoot, $this->testOutputDir);

        ob_start();
        $exitCode = $cmd->run(['--config', '--dir=' . $this->testOutputDir . '/out']);
        ob_end_clean();

        $this->assertEquals(0, $exitCode);
        $this->assertFileExists($this->testOutputDir . '/out/config/legal-pages.php');
        $this->assertFileDoesNotExist($this->testOutputDir . '/out/generate.php');
        $this->assertFileDoesNotExist($this->testOutputDir . '/out/legal-generator.html');
    }

    public function testPublishUiOnly()
    {
        $cmd = new PublishCommand($this->packageRoot, $this->testOutputDir);

        ob_start();
        $exitCode = $cmd->run(['--ui', '--dir=' . $this->testOutputDir . '/out']);
        ob_end_clean();

        $this->assertEquals(0, $exitCode);
        $this->assertFileExists($this->testOutputDir . '/out/legal-generator.html');
        $this->assertFileExists($this->testOutputDir . '/out/js/legal-generator.js');
        $this->assertFileExists($this->testOutputDir . '/out/ajax/ajax_legal_handler.php');
        $this->assertFileDoesNotExist($this->testOutputDir . '/out/config/legal-pages.php');
    }

    public function testPublishQuickstartOnly()
    {
        $cmd = new PublishCommand($this->packageRoot, $this->testOutputDir);

        ob_start();
        $exitCode = $cmd->run(['--quickstart', '--dir=' . $this->testOutputDir . '/out']);
        ob_end_clean();

        $this->assertEquals(0, $exitCode);
        $this->assertFileExists($this->testOutputDir . '/out/generate.php');
        $this->assertFileDoesNotExist($this->testOutputDir . '/out/config/legal-pages.php');
        $this->assertFileDoesNotExist($this->testOutputDir . '/out/legal-generator.html');
    }

    public function testJsPathRewriting()
    {
        $cmd = new PublishCommand($this->packageRoot, $this->testOutputDir);

        ob_start();
        $cmd->run(['--ui', '--dir=' . $this->testOutputDir . '/out']);
        ob_end_clean();

        $jsContent = file_get_contents($this->testOutputDir . '/out/js/legal-generator.js');

        // Should NOT contain absolute paths
        $this->assertStringNotContainsString("'/ajax/ajax_legal_handler.php'", $jsContent);
        // Should contain relative paths
        $this->assertStringContainsString("'ajax/ajax_legal_handler.php'", $jsContent);
    }

    public function testAjaxAutoloadPathRewriting()
    {
        $cmd = new PublishCommand($this->packageRoot, $this->testOutputDir);

        ob_start();
        $cmd->run(['--ui', '--dir=' . $this->testOutputDir . '/out']);
        ob_end_clean();

        $ajaxContent = file_get_contents($this->testOutputDir . '/out/ajax/ajax_legal_handler.php');

        // Should have adjusted autoloader path
        $this->assertStringContainsString("/../../vendor/autoload.php", $ajaxContent);
    }

    public function testSkipsExistingFiles()
    {
        $outDir = $this->testOutputDir . '/out';

        // Pre-create a file
        mkdir($outDir . '/config', 0755, true);
        file_put_contents($outDir . '/config/legal-pages.php', 'existing content');

        $cmd = new PublishCommand($this->packageRoot, $this->testOutputDir);

        ob_start();
        $exitCode = $cmd->run(['--config', '--dir=' . $outDir]);
        $output = ob_get_clean();

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('[SKIP]', $output);
        // Original content should be preserved
        $this->assertEquals('existing content', file_get_contents($outDir . '/config/legal-pages.php'));
    }

    public function testDefaultDirIsLegalPages()
    {
        $cmd = new PublishCommand($this->packageRoot, $this->testOutputDir);

        ob_start();
        $exitCode = $cmd->run(['--config']);
        ob_end_clean();

        $this->assertEquals(0, $exitCode);
        $this->assertFileExists($this->testOutputDir . '/legal-pages/config/legal-pages.php');
    }
}
```

- [ ] **Step 2: Run tests**

Run: `vendor/bin/phpunit tests/Unit/PublishCommandTest.php`
Expected: All 8 tests pass

- [ ] **Step 3: Commit**

```bash
git add tests/Unit/PublishCommandTest.php
git commit -m "test: add PublishCommand tests for all publish scenarios"
```

---

## Chunk 2: README Update

### Task 5: Restructure README with Getting Started at top

**Files:**
- Modify: `README.md`

- [ ] **Step 1: Replace the Quick Start section at the top of README.md**

Replace the current opening section (everything from `## Quick Start` through the first code block that ends with `$gen->savePage(...)`) with a new Getting Started section. The new section goes right after the package description line:

```markdown
## Getting Started

### 1. Install

```bash
composer require yohns/legal-pages
```

### 2. Publish starter files

```bash
php vendor/bin/legal-pages publish
```

This copies config, a quickstart script, and optionally the web UI into `legal-pages/` in your project:

```
legal-pages/
  config/legal-pages.php    ← Edit with your site details
  generate.php              ← Run to generate pages
  legal-generator.html      ← Web-based UI (optional)
  js/legal-generator.js
  ajax/ajax_legal_handler.php
```

### 3. Generate your legal pages

```bash
# Edit your details
nano legal-pages/config/legal-pages.php

# Generate pages
php legal-pages/generate.php
```

Output appears in `legal-pages/generated/` as both Markdown and Bootstrap 5.3.8 HTML files.

### Publish Options

```bash
# Publish everything (default)
php vendor/bin/legal-pages publish

# Publish only what you need
php vendor/bin/legal-pages publish --config       # Just the config file
php vendor/bin/legal-pages publish --quickstart    # Just generate.php
php vendor/bin/legal-pages publish --ui            # Just the web UI

# Custom output directory
php vendor/bin/legal-pages publish --dir=public/legal
```
```

Keep the rest of the README as-is (Output Formats, Available Templates, etc.), but remove the old `## Quick Start` section since it's replaced.

- [ ] **Step 2: Run all tests to ensure nothing is broken**

Run: `vendor/bin/phpunit tests/`
Expected: All tests pass (10 original + 8 new = 18 total)

- [ ] **Step 3: Commit**

```bash
git add README.md
git commit -m "docs: restructure README with Getting Started publish workflow at top"
```

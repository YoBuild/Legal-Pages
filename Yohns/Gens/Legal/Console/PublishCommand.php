<?php

namespace Yohns\Gens\Legal\Console;

class PublishCommand {
	private string $packageRoot;
	private string $projectRoot;
	private string $outputDir;
	private bool   $publishConfig     = false;
	private bool   $publishQuickstart = false;
	private bool   $publishUi         = false;
	private int    $copiedCount       = 0;
	private int    $skippedCount      = 0;

	public function __construct(string $packageRoot, string $projectRoot) {
		$this->packageRoot = rtrim($packageRoot, '/\\');
		$this->projectRoot = rtrim($projectRoot, '/\\');
	}

	/**
	 * Parse CLI arguments and run the publish command.
	 *
	 * @param array $argv CLI arguments
	 * @return int Exit code (0 = success, 1 = error)
	 */
	public function run(array $argv): int {
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

	private function parseArgs(array $argv): void {
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
				if (!str_starts_with($dir, '/') && !preg_match('/^[A-Z]:/i', $dir)) {
					$this->outputDir = $this->projectRoot . '/' . $dir;
				} else {
					$this->outputDir = $dir;
				}
			}
		}

		if (!$hasFlags) {
			$this->publishConfig = true;
			$this->publishQuickstart = true;
			$this->publishUi = true;
		}
	}

	private function copyFile(string $source, string $dest): void {
		$this->copyFileWithTransform($source, $dest, null);
	}

	private function copyFileWithTransform(string $source, string $dest, ?callable $transform): void {
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

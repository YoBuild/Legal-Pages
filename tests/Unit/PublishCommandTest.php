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

        $this->assertStringNotContainsString("'/ajax/ajax_legal_handler.php'", $jsContent);
        $this->assertStringContainsString("'ajax/ajax_legal_handler.php'", $jsContent);
    }

    public function testAjaxAutoloadPathRewriting()
    {
        $cmd = new PublishCommand($this->packageRoot, $this->testOutputDir);

        ob_start();
        $cmd->run(['--ui', '--dir=' . $this->testOutputDir . '/out']);
        ob_end_clean();

        $ajaxContent = file_get_contents($this->testOutputDir . '/out/ajax/ajax_legal_handler.php');

        $this->assertStringContainsString("/../../vendor/autoload.php", $ajaxContent);
    }

    public function testSkipsExistingFiles()
    {
        $outDir = $this->testOutputDir . '/out';

        mkdir($outDir . '/config', 0755, true);
        file_put_contents($outDir . '/config/legal-pages.php', 'existing content');

        $cmd = new PublishCommand($this->packageRoot, $this->testOutputDir);

        ob_start();
        $exitCode = $cmd->run(['--config', '--dir=' . $outDir]);
        $output = ob_get_clean();

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('[SKIP]', $output);
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

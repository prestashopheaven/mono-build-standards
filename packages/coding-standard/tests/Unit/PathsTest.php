<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/MIT
 *
 * @author    prestahaven.com
 * @copyright Since 2026 George van Engers
 * @license   https://opensource.org/licenses/MIT MIT License
 */

declare(strict_types=1);

namespace PrestaShopHeaven\Build\CodingStandard\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PrestaShopHeaven\Build\CodingStandard\Paths;

/**
 * The paths of the shared configs, and the content of each config: the same values the modules have today.
 */
final class PathsTest extends TestCase
{
    /**
     * Every path is absolute, uses "/" and points to an existing file in the package folder.
     */
    public function testEveryPathExistsInThePackage(): void
    {
        $root = Paths::root();
        self::assertSame(str_replace('\\', '/', (string) realpath(__DIR__ . '/../..')), str_replace('\\', '/', (string) realpath($root)));
        self::assertStringNotContainsString('\\', $root);
        self::assertFileExists($root . '/composer.json');

        self::assertSame("$root/php-cs-fixer.php", Paths::phpCsFixer());
        self::assertSame("$root/phpstan.neon", Paths::phpstanNeon());
        self::assertSame("$root/PrestaHavenCompatibility", Paths::phpcsStandard());
        self::assertSame("$root/PrestaHavenCompatibility/ruleset.xml", Paths::phpcsRuleset());
        self::assertSame("$root/resources/phpunit.xml.dist", Paths::phpunitTemplate());
        self::assertDirectoryExists(Paths::phpcsStandard());

        $all = Paths::all();
        self::assertSame(['php-cs-fixer', 'phpstan', 'phpcs', 'phpunit'], array_keys($all));
        foreach ($all as $file) {
            self::assertFileExists($file);
        }
    }

    /**
     * The ruleset is valid XML: standard PrestaHavenCompatibility, PHPCompatibility with testVersion 8.1-, the
     * excluded folders and arguments of the modules' phpcs.xml.dist, and no <file> (that stays in the module).
     */
    public function testTheRulesetIsPhpCompatibilityFromPhp81(): void
    {
        $xml = simplexml_load_file(Paths::phpcsRuleset());
        self::assertNotFalse($xml);

        self::assertSame(Paths::PHPCS_STANDARD, (string) $xml['name']);
        self::assertSame('PrestaHavenCompatibility', basename(Paths::phpcsStandard()));
        self::assertSame(['PHPCompatibility'], array_map('strval', $xml->xpath('/ruleset/rule/@ref') ?: []));
        self::assertSame(['8.1-'], array_map('strval', $xml->xpath('/ruleset/config[@name="testVersion"]/@value') ?: []));
        self::assertSame(['*/vendor/*', '*/node_modules/*', '*/.cache/*'], array_map('strval', $xml->xpath('/ruleset/exclude-pattern') ?: []));
        self::assertSame(['php'], array_map('strval', $xml->xpath('/ruleset/arg[@name="extensions"]/@value') ?: []));
        self::assertSame(['ps'], array_map('strval', $xml->xpath('/ruleset/arg[not(@name)]/@value') ?: []));
        self::assertCount(1, $xml->xpath('/ruleset/arg[@name="colors"]') ?: []);
        self::assertSame([], $xml->xpath('/ruleset/file') ?: []);
    }

    /**
     * PHPCS with a module-style phpcs.xml.dist (only <file> and the standard) reports PHP 8.2 code and skips vendor/,
     * node_modules/ and .cache/; the module can still raise testVersion. The standard is referenced by its folder, so
     * this does not depend on the installer plugin; registration by name is checked in the next test.
     */
    public function testPhpcsChecksAModuleWithTheStandard(): void
    {
        $dir = str_replace('\\', '/', sys_get_temp_dir()) . '/phpcs-standard-' . bin2hex(random_bytes(4));
        $php82 = "<?php\nreadonly class A\n{\n}\n";
        foreach (['src', 'vendor/acme', 'node_modules/acme', '.cache'] as $sub) {
            mkdir("$dir/$sub", 0777, true);
            file_put_contents("$dir/$sub/A.php", $php82);
        }
        $ruleset = static fn (string $extra): string => "<?xml version=\"1.0\"?>\n<ruleset name=\"test\">\n    <file>.</file>\n$extra    <rule ref=\""
            . Paths::phpcsStandard() . "\"/>\n</ruleset>\n";
        file_put_contents("$dir/phpcs.xml.dist", $ruleset(''));
        file_put_contents("$dir/php82.xml", $ruleset("    <config name=\"testVersion\" value=\"8.2-\"/>\n"));

        try {
            [$code, $output] = $this->phpcs($dir, ['--standard=phpcs.xml.dist', '--report=json', '--no-colors', '-q']);
            $report = json_decode($output, true);
            self::assertIsArray($report, $output);
            self::assertSame(2, $code, $output);
            $files = array_map(static fn (string $f): string => str_replace('\\', '/', $f), array_keys($report['files']));
            self::assertSame(["$dir/src/A.php"], array_map(static fn (string $f): string => str_replace(str_replace('\\', '/', (string) realpath($dir)), $dir, $f), $files));
            self::assertSame('PHPCompatibility.Classes.NewReadonlyClasses.Found', $report['files'][array_key_first($report['files'])]['messages'][0]['source']);

            [$code, $output] = $this->phpcs($dir, ['--standard=php82.xml', '--report=json', '--no-colors', '-q']);
            self::assertSame(0, $code, $output);
        } finally {
            foreach (['src', 'vendor/acme', 'node_modules/acme', '.cache'] as $sub) {
                unlink("$dir/$sub/A.php");
            }
            foreach (['vendor/acme', 'vendor', 'node_modules/acme', 'node_modules', 'src', '.cache'] as $sub) {
                rmdir("$dir/$sub");
            }
            unlink("$dir/phpcs.xml.dist");
            unlink("$dir/php82.xml");
            rmdir($dir);
        }
    }

    /**
     * The package type phpcodesniffer-standard makes the installer plugin register it: phpcs -i lists the standard.
     */
    public function testTheStandardIsRegisteredByName(): void
    {
        $composer = json_decode((string) file_get_contents(Paths::root() . '/composer.json'), true);
        self::assertSame('phpcodesniffer-standard', $composer['type']);

        [$code, $output] = $this->phpcs(Paths::root(), ['-i']);

        self::assertSame(0, $code, $output);
        self::assertStringContainsString(Paths::PHPCS_STANDARD, $output);
    }

    /**
     * The neon has the values of the modules' tests/phpstan/phpstan.neon: level 6, PHP 8.1, the PrestaShop module
     * extension from the module folder, index.php excluded, no unmatched-ignore errors, the cache in .cache/phpstan.
     */
    public function testThePhpstanNeonHasTheModuleValues(): void
    {
        $neon = self::parseNeon((string) file_get_contents(Paths::phpstanNeon()));

        self::assertSame(['includes', 'parameters'], array_keys($neon));
        self::assertSame(['%currentWorkingDirectory%/vendor/prestashop/php-dev-tools/phpstan/ps-module-extension.neon'], $neon['includes']);
        self::assertSame([
            'level' => '6',
            'phpVersion' => '80100',
            'excludePaths' => ['*/index.php'],
            'reportUnmatchedIgnoredErrors' => 'false',
            'tmpDir' => '%currentWorkingDirectory%/.cache/phpstan',
        ], $neon['parameters']);
    }

    /**
     * The PHPUnit template is the modules' phpunit.xml.dist: unit and integration suites, the unit bootstrap, src/ as
     * source, cache in .cache/phpunit, risky tests and warnings fail.
     */
    public function testThePhpunitTemplateIsTheModuleConfig(): void
    {
        $xml = simplexml_load_file(Paths::phpunitTemplate());
        self::assertNotFalse($xml);

        self::assertSame('tests/Unit/bootstrap.php', (string) $xml['bootstrap']);
        self::assertSame('.cache/phpunit', (string) $xml['cacheDirectory']);
        self::assertSame('true', (string) $xml['failOnRisky']);
        self::assertSame('true', (string) $xml['failOnWarning']);
        self::assertSame(['unit', 'integration'], array_map('strval', $xml->xpath('/phpunit/testsuites/testsuite/@name') ?: []));
        self::assertSame(['tests/Unit', 'tests/Integration'], array_map('strval', $xml->xpath('/phpunit/testsuites/testsuite/directory') ?: []));
        self::assertSame(['src'], array_map('strval', $xml->xpath('/phpunit/source/include/directory') ?: []));
    }

    /**
     * Runs PHPCS of the installation with PHP in $cwd; returns the exit code and the output.
     *
     * @param list<string> $args
     *
     * @return array{0: int, 1: string}
     */
    private function phpcs(string $cwd, array $args): array
    {
        $bin = \Composer\InstalledVersions::getInstallPath('squizlabs/php_codesniffer') . '/bin/phpcs';
        $command = array_merge([PHP_BINARY, $bin], $args);
        $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $cwd);
        self::assertIsResource($process);
        $output = (string) stream_get_contents($pipes[1]) . (string) stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        return [proc_close($process), $output];
    }

    /**
     * A small parser for the neon subset of phpstan.neon: comments, "key:" blocks, "key: value" and "- item" lists,
     * nested by indentation. Values stay strings.
     *
     * @return array<string, mixed>
     */
    private static function parseNeon(string $text): array
    {
        $root = [];
        $stack = [[-1, &$root]];
        foreach (preg_split('/\R/', $text) ?: [] as $line) {
            if (trim($line, " \n\r\t\v\0") === '' || str_starts_with(ltrim($line, " \n\r\t\v\0"), '#')) {
                continue;
            }
            $indent = strlen($line) - strlen(ltrim($line, " \n\r\t\v\0"));
            while ($indent <= $stack[count($stack) - 1][0]) {
                array_pop($stack);
            }
            $parent = &$stack[count($stack) - 1][1];
            $item = trim($line, " \n\r\t\v\0");
            if (str_starts_with($item, '- ')) {
                $parent[] = substr($item, 2);
            } elseif (preg_match('/^([A-Za-z]+):\s*(.*)$/', $item, $m) === 1 && $m[2] !== '') {
                $parent[$m[1]] = $m[2];
            } elseif (preg_match('/^([A-Za-z]+):$/', $item, $m) === 1) {
                $parent[$m[1]] = [];
                $stack[] = [$indent, &$parent[$m[1]]];
            } else {
                self::fail("Unexpected neon line: $line");
            }
            unset($parent);
        }

        return $root;
    }
}

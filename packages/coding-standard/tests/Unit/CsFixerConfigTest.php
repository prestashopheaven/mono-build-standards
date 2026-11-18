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

use PhpCsFixer\ConfigInterface;
use PhpCsFixer\Finder;
use PHPUnit\Framework\TestCase;
use PrestaShop\CodingStandards\CsFixer\Config;
use PrestaShopHeaven\Build\CodingStandard\CsFixerConfig;
use PrestaShopHeaven\Build\CodingStandard\Paths;

/**
 * The PHP-CS-Fixer config: the PrestaShop rules plus the house rule, the finder exclusions and the cache file,
 * the same as module-dev-tools' resources/php-cs-fixer.php.
 */
final class CsFixerConfigTest extends TestCase
{
    private string $dir;

    /**
     * A project folder with a PHP file in src/ and in every excluded folder.
     */
    protected function setUp(): void
    {
        $this->dir = str_replace('\\', '/', sys_get_temp_dir()) . '/cs-fixer-config-' . bin2hex(random_bytes(4));
        foreach (['src', 'vendor/acme', 'node_modules/acme', '.cache', 'tests/behat', 'tests/Unit'] as $sub) {
            mkdir("$this->dir/$sub", 0777, true);
            file_put_contents("$this->dir/$sub/A.php", "<?php\n");
        }
        file_put_contents("$this->dir/src/readme.txt", "not PHP\n");
        file_put_contents("$this->dir/module.php", "<?php\n");
    }

    /**
     * Removes the project folder.
     */
    protected function tearDown(): void
    {
        $items = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->dir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($this->dir);
    }

    /**
     * The rules are the PrestaShop standard with blank_line_after_opening_tag switched off, nothing else.
     */
    public function testGetRulesIsThePrestaShopStandardPlusTheHouseRule(): void
    {
        $rules = CsFixerConfig::create($this->dir)->getRules();
        $prestashop = (new Config())->getRules();

        self::assertFalse($rules['blank_line_after_opening_tag']);
        self::assertSame(['blank_line_after_opening_tag' => false], CsFixerConfig::HOUSE_RULES);
        self::assertSame(array_merge($prestashop, CsFixerConfig::HOUSE_RULES), $rules);
        self::assertTrue($rules['@Symfony']);
        self::assertSame(['spacing' => 'one'], $rules['concat_space']);
    }

    /**
     * The config is a PrestaShop config with risky rules allowed and the cache in .cache/.
     */
    public function testCreateUsesTheCacheInTheProjectFolder(): void
    {
        $config = CsFixerConfig::create($this->dir);

        self::assertInstanceOf(ConfigInterface::class, $config);
        self::assertInstanceOf(Config::class, $config);
        self::assertTrue($config->getRiskyAllowed());
        self::assertTrue($config->getUsingCache());
        self::assertSame("$this->dir/.cache/.php-cs-fixer.cache", $config->getCacheFile());
        self::assertSame('.cache/.php-cs-fixer.cache', CsFixerConfig::CACHE_FILE);
    }

    /**
     * The finder visits every PHP file below the project folder except vendor, node_modules, .cache and tests/behat.
     */
    public function testCreateExcludesTheToolFolders(): void
    {
        $finder = CsFixerConfig::create($this->dir)->getFinder();
        self::assertInstanceOf(Finder::class, $finder);

        $files = [];
        foreach ($finder as $file) {
            $files[] = substr(str_replace('\\', '/', $file->getPathname()), strlen($this->dir) + 1);
        }
        sort($files);

        self::assertSame(['module.php', 'src/A.php', 'tests/Unit/A.php'], $files);
        self::assertSame(['vendor', 'node_modules', '.cache', 'tests/behat'], CsFixerConfig::EXCLUDED_DIRS);
    }

    /**
     * A folder that does not exist is refused by the finder (wrong input), as with the old factory.
     */
    public function testCreateWithAMissingFolderFails(): void
    {
        $this->expectException(\Symfony\Component\Finder\Exception\DirectoryNotFoundException::class);

        CsFixerConfig::create($this->dir . '/missing');
    }

    /**
     * php-cs-fixer.php returns the factory a module's .php-cs-fixer.dist.php calls with its folder.
     */
    public function testTheFactoryFileReturnsTheSameConfig(): void
    {
        $factory = require Paths::phpCsFixer();
        self::assertInstanceOf(\Closure::class, $factory);

        $config = $factory($this->dir);

        self::assertInstanceOf(CsFixerConfig::class, $config);
        self::assertSame(CsFixerConfig::create($this->dir)->getRules(), $config->getRules());
        self::assertSame("$this->dir/.cache/.php-cs-fixer.cache", $config->getCacheFile());
    }

    /**
     * The CPU guard of the Module-Factory: a quarter of the cores (at least 1), or PS_MODULE_CPU_WORKERS, and the
     * config uses that number of worker processes.
     */
    public function testWorkersAreLimited(): void
    {
        $saved = getenv('PS_MODULE_CPU_WORKERS');
        try {
            putenv('PS_MODULE_CPU_WORKERS');
            self::assertSame(8, CsFixerConfig::workers(32));
            self::assertSame(1, CsFixerConfig::workers(3));
            self::assertGreaterThanOrEqual(1, CsFixerConfig::workers());
            self::assertSame(CsFixerConfig::workers(), CsFixerConfig::create($this->dir)->getParallelConfig()->getMaxProcesses());

            putenv('PS_MODULE_CPU_WORKERS=3');
            self::assertSame(3, CsFixerConfig::workers(32));
            putenv('PS_MODULE_CPU_WORKERS=0');
            self::assertSame(8, CsFixerConfig::workers(32), 'zero is not a setting');
            putenv('PS_MODULE_CPU_WORKERS=many');
            self::assertSame(8, CsFixerConfig::workers(32), 'text is not a setting');
        } finally {
            putenv($saved === false ? 'PS_MODULE_CPU_WORKERS' : "PS_MODULE_CPU_WORKERS=$saved");
        }
    }
}

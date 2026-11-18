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

namespace PrestaShopHeaven\Build\CodingStandard;

use PhpCsFixer\ConfigInterface;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfig;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;
use PrestaShop\CodingStandards\CsFixer\Config;

/**
 * The PHP-CS-Fixer configuration of every PrestaHaven module and package.
 *
 * Base: the PrestaShop coding standard (prestashop/php-dev-tools), plus the rule from its newer releases that keeps
 * the license header directly after <?php, like PrestaShop's own files and the Addons validator expect. A module's
 * .php-cs-fixer.dist.php returns CsFixerConfig::create(__DIR__), or the factory in php-cs-fixer.php of this package.
 */
final class CsFixerConfig extends Config
{
    /** Folders below the project folder that are never fixed. */
    public const EXCLUDED_DIRS = ['vendor', 'node_modules', '.cache', 'tests/behat'];

    /** The cache file, relative to the project folder. */
    public const CACHE_FILE = '.cache/.php-cs-fixer.cache';

    /** The rules this standard adds to (or changes in) the PrestaShop coding standard. */
    public const HOUSE_RULES = ['blank_line_after_opening_tag' => false];

    /**
     * The configuration for the project in $dir: every PHP file below it except the excluded folders, with the
     * cache in $dir/.cache.
     */
    public static function create(string $dir): ConfigInterface
    {
        $config = new self();
        /** @var Finder $finder */
        $finder = $config->setUsingCache(true)->getFinder();
        $finder->in($dir)->exclude(self::EXCLUDED_DIRS);
        $config->setCacheFile($dir . '/' . self::CACHE_FILE);
        // CPU guard of the Module-Factory (F9): a limited number of worker processes, never every core.
        $config->setParallelConfig(new ParallelConfig(self::workers()));

        return $config;
    }

    /**
     * The worker processes for PHP-CS-Fixer: PS_MODULE_CPU_WORKERS when it is a positive number, otherwise a quarter of
     * the cores (at least 1), like the CPU guard of build-tools.
     *
     * @param int|null $cores the number of cores (detected by PHP-CS-Fixer by default)
     */
    public static function workers(?int $cores = null): int
    {
        $setting = getenv('PS_MODULE_CPU_WORKERS');
        if (is_string($setting) && preg_match('/^\d+$/', $setting) === 1 && (int) $setting > 0) {
            return (int) $setting;
        }

        return max(1, intdiv($cores ?? ParallelConfigFactory::detect()->getMaxProcesses(), 4));
    }

    /**
     * Returns the PrestaShop rules with the house additions.
     *
     * @return array<string, mixed>
     */
    public function getRules(): array
    {
        return array_merge(parent::getRules(), self::HOUSE_RULES);
    }
}

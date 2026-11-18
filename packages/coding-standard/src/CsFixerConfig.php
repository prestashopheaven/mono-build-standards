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

        return $config;
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

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

/**
 * The absolute paths of the shared configs of this package, so build-tools (the scaffold, ps-module mono check) can
 * find them wherever Composer installed the package. Paths use "/" on every OS.
 */
final class Paths
{
    /** The name of the PHPCS standard (the folder with its ruleset.xml). */
    public const PHPCS_STANDARD = 'PrestaHavenCompatibility';

    /**
     * The package folder.
     */
    public static function root(): string
    {
        return str_replace('\\', '/', dirname(__DIR__));
    }

    /**
     * php-cs-fixer.php: returns the factory fn(string $dir): ConfigInterface.
     */
    public static function phpCsFixer(): string
    {
        return self::root() . '/php-cs-fixer.php';
    }

    /**
     * phpstan.neon: the base PHPStan config a module's tests/phpstan/phpstan.neon includes.
     */
    public static function phpstanNeon(): string
    {
        return self::root() . '/phpstan.neon';
    }

    /**
     * The folder of the PHPCS standard PrestaHavenCompatibility (a valid --standard value by itself).
     */
    public static function phpcsStandard(): string
    {
        return self::root() . '/' . self::PHPCS_STANDARD;
    }

    /**
     * The ruleset.xml of the PHPCS standard PrestaHavenCompatibility.
     */
    public static function phpcsRuleset(): string
    {
        return self::phpcsStandard() . '/ruleset.xml';
    }

    /**
     * resources/phpunit.xml.dist: the template the scaffold copies into a module (PHPUnit cannot include configs).
     */
    public static function phpunitTemplate(): string
    {
        return self::root() . '/resources/phpunit.xml.dist';
    }

    /**
     * Every config of this package by name, for listings and checks.
     *
     * @return array<string, string>
     */
    public static function all(): array
    {
        return [
            'php-cs-fixer' => self::phpCsFixer(),
            'phpstan' => self::phpstanNeon(),
            'phpcs' => self::phpcsRuleset(),
            'phpunit' => self::phpunitTemplate(),
        ];
    }
}

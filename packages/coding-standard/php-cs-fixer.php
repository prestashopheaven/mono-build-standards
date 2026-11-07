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

/*
 * The shared PHP-CS-Fixer configuration as a factory, the same contract as module-dev-tools'
 * resources/php-cs-fixer.php. Use it in .php-cs-fixer.dist.php:
 *
 *   $factory = require __DIR__ . '/vendor/prestashopheaven/build-coding-standard/php-cs-fixer.php';
 *
 *   return $factory(__DIR__);
 */

declare(strict_types=1);

use PrestaShopHeaven\Build\CodingStandard\CsFixerConfig;

return static fn (string $dir): PhpCsFixer\ConfigInterface => CsFixerConfig::create($dir);

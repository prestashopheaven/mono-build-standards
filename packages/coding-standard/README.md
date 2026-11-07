# prestashopheaven/build-coding-standard

The shared PHP coding standard of the PrestaHaven modules and packages: the PHP-CS-Fixer config, the base PHPStan
config, the PHPCS standard `PrestaHavenCompatibility` and the PHPUnit config template. A build package: `require-dev`
only, never in a module zip (PKG-03).

## Use when

A module or package needs its `.php-cs-fixer.dist.php`, `tests/phpstan/phpstan.neon`, `phpcs.xml.dist` or
`phpunit.xml.dist`: include the shared base and add only its own exceptions.

## Install

In the module's `composer.json` (the last two are already there in every module):

```json
"require-dev": {
    "dealerdirect/phpcodesniffer-composer-installer": "^1.2",
    "phpcompatibility/php-compatibility": "^10.0@alpha",
    "prestashopheaven/build-coding-standard": "^1.0"
},
"config": {
    "allow-plugins": {
        "dealerdirect/phpcodesniffer-composer-installer": true
    }
}
```

`phpcompatibility/php-compatibility` is an alpha: Composer only takes the `@alpha` flag from the root package, so the
module requires it itself (this package only suggests it).

## Usage

### PHP-CS-Fixer: `.php-cs-fixer.dist.php`

```php
<?php
/**
 * (the module's license header)
 */

/*
 * Coding standard shared by all PrestaHaven modules (package build-coding-standard).
 */
$factory = require __DIR__ . '/vendor/prestashopheaven/build-coding-standard/php-cs-fixer.php';

return $factory(__DIR__);
```

The factory is `fn (string $dir): PhpCsFixer\ConfigInterface`, the same contract as module-dev-tools'
`resources/php-cs-fixer.php`. It returns `PrestaShopHeaven\Build\CodingStandard\CsFixerConfig::create($dir)` (that call
works directly too): the PrestaShop coding standard (`prestashop/php-dev-tools`) plus `blank_line_after_opening_tag`
off (the license header directly after `<?php`, as the Addons validator expects); every PHP file below `$dir` except
`vendor/`, `node_modules/`, `.cache/` and `tests/behat/`; the cache in `.cache/.php-cs-fixer.cache`.

### PHPStan: `tests/phpstan/phpstan.neon`

```neon
includes:
    - ../../vendor/prestashopheaven/build-coding-standard/phpstan.neon
```

The base: level 6, `phpVersion: 80100`, the PrestaShop module extension of `prestashop/php-dev-tools` (via
`%currentWorkingDirectory%`: `ps-module phpstan` runs from the module folder), `excludePaths: [*/index.php]`,
`reportUnmatchedIgnoredErrors: false`, `tmpDir: %currentWorkingDirectory%/.cache/phpstan`. Neon resolves the include
relative to the including file, so the path is `../../vendor/...` from `tests/phpstan/`. A module adds only its own
exceptions below the include, for example (`heavenproductstocksorting`):

```neon
parameters:
    scanDirectories:
        - %env._PS_ROOT_DIR_%/modules/ps_facetedsearch/src
```

`tests/phpstan/config.json` (PrestaShop versions, cache dir) stays in the module.

### PHPCS: `phpcs.xml.dist`

```xml
<?xml version="1.0"?>
<ruleset name="heavenfoo PHP compatibility">
    <description>Checks that the code runs on PHP 8.1 and newer.</description>

    <file>.</file>
    <rule ref="PrestaHavenCompatibility"/>
</ruleset>
```

The standard `PrestaHavenCompatibility` (`PrestaHavenCompatibility/ruleset.xml`) holds what every module had in its own
ruleset: `PHPCompatibility` with `testVersion` `8.1-`, the exclude patterns `*/vendor/*`, `*/node_modules/*`,
`*/.cache/*` and the arguments `extensions=php`, `colors`, `-ps`. PHPCS applies them from an included ruleset; the
module's own ruleset wins (for example `<config name="testVersion" value="8.2-"/>` above the rule). `<file>` stays in the
module: PHPCS would read a `<file>` of an included ruleset relative to the standard's folder.

**Registration.** This package has the Composer type `phpcodesniffer-standard`: that is the only thing
`dealerdirect/phpcodesniffer-composer-installer` looks for, and it then adds the package folder to PHPCS'
`installed_paths` (`vendor/bin/phpcs -i` lists `PrestaHavenCompatibility`). For Composer itself the type behaves like
`library` (installed in `vendor/`, autoloaded). Without the plugin, reference the standard by path:
`<rule ref="vendor/prestashopheaven/build-coding-standard/PrestaHavenCompatibility"/>`.

### PHPUnit: `phpunit.xml.dist`

PHPUnit cannot include another config, so the scaffold copies `resources/phpunit.xml.dist` into the module: the suites
`unit` (`tests/Unit`) and `integration` (`tests/Integration`), bootstrap `tests/Unit/bootstrap.php`, source `src/`,
cache in `.cache/phpunit`, risky tests and warnings fail.

### Paths for build-tools

`PrestaShopHeaven\Build\CodingStandard\Paths` gives the absolute path of every config, wherever Composer installed the
package: `phpCsFixer()`, `phpstanNeon()`, `phpcsStandard()` (a valid `--standard` value), `phpcsRuleset()`,
`phpunitTemplate()` and `all()`.

---

Read-only split of [`prestashopheaven/mono-build-standards`](https://github.com/prestashopheaven/mono-build-standards): changes,
issues and pull requests go there. MIT License.

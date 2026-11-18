# Changelog

The version is the one of the monorepo `mono-build-standards`.

## 1.2.0

- `CsFixerConfig::create()` no longer limits the workers (`PS_MODULE_CPU_WORKERS` is gone): PHP-CS-Fixer's own
  parallel setting again.

## 1.1.0

- `CsFixerConfig::create()` sets a limited `ParallelConfig`: `PS_MODULE_CPU_WORKERS`, or a quarter of the cores (at
  least 1), instead of every core (CPU guard of the Module-Factory: the PC stays below 80 % CPU).

## 1.0.0

First version. The sources of truth were the copies in the 17 modules (scan of 2026-11-07) and
`resources/php-cs-fixer.php` of build-tools 1.0.0 (module-dev-tools 1.26.0).

- `CsFixerConfig` (`src/`) and the factory `php-cs-fixer.php`: the same rules, finder exclusions (`vendor`,
  `node_modules`, `.cache`, `tests/behat`), cache file and risky setting as the factory of module-dev-tools. Checked on
  `heavenautoorderstatus`: the same rules and the same 383 files. `.php-cs-fixer.dist.php` was identical in all 17
  modules.
- `phpstan.neon`: the base of `tests/phpstan/phpstan.neon`, identical in 16 modules. The 17th
  (`heavenproductstocksorting`) adds only `scanDirectories` for ps_facetedsearch: that is a module exception and stays in
  the module, below the include. `excludePaths: */index.php` keeps working from `vendor/`: PHPStan leaves paths that start
  with `*` as they are (they are not resolved relative to the config file).
- `PrestaHavenCompatibility/ruleset.xml`: the PHPCS standard with what `phpcs.xml.dist` had in all 17 modules (identical
  apart from the module name): PHPCompatibility, `testVersion` `8.1-`, exclude `vendor`, `node_modules`, `.cache`, the
  arguments `extensions=php`, `colors`, `-ps`. `<file>.</file>` stays in the module's ruleset (PHPCS would resolve an
  included `<file>` against the standard's folder).
- Package type `phpcodesniffer-standard` instead of `library`, so the installer plugin registers the standard by name.
  Composer installs and autoloads it like a library; the monorepo rules do not check the type.
- `resources/phpunit.xml.dist`: `phpunit.xml.dist`, identical in all 17 modules (a template: PHPUnit cannot include).
- `Paths`: the absolute paths of the configs, for build-tools.
- Verified on a throwaway copy of `heavenproductstocksorting` with the package installed through Composer and the three
  configs reduced to the lines in the README: `composer cs` (0 of 222 files), `composer compat` and PHPStan against
  PrestaShop 8.1.0 and 9.2.0 green; an injected readonly class and an untyped array are reported.

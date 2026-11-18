# Changelog

All packages of this monorepo share one version. Per package details: its own `CHANGELOG.md`.

## 1.2.0

- `build-coding-standard`: PHP-CS-Fixer uses its own number of workers again (the CPU guard is removed, 2026-11-18).

## 1.1.0

- `build-coding-standard`: PHP-CS-Fixer runs with a limited number of worker processes (CPU guard of the
  Module-Factory, 2026-11-18).

## 1.0.0

- Monorepo created (2026-11-07), milestone M3 of the package collection (`docs/packages/STATUS.md`).
- `build-coding-standard` 1.0.0: `CsFixerConfig` + `php-cs-fixer.php` (the factory of module-dev-tools, same rules and
  finder), base `phpstan.neon`, PHPCS standard `PrestaHavenCompatibility` (package type `phpcodesniffer-standard`),
  `resources/phpunit.xml.dist`, `Paths`. A module's configs shrink to an include plus its own exceptions.
- `build-typescript` 1.0.0: `tsconfig.base.json`, the PrestaShop typings (admin, front), `build.mjs`,
  `eslint.config.mjs` (typescript and plain mode, merged from the two module variants) and `vitest.config.mjs` as
  functions the module calls with its `import.meta.url`, `package.base.json` per mode, `Paths`. Verified on throwaway
  copies of three modules: byte-identical builds.

# mono-build-standards

Shared coding standards and configs of the PrestaHaven modules: PHP-CS-Fixer, PHPStan, PHPCS compatibility, TypeScript, ESLint, Vitest and the JS build.

A monorepo of the PrestaHaven package collection: every folder in `packages/` is one Composer package, published as a
read-only repo `prestashopheaven/build-<folder>` on Packagist. All packages of this monorepo share one version.
The plan, the rules and the other monorepos: `docs/packages/` in the PrestaHaven workspace.

## Packages

| Package | Use when |
|---|---|
| [`prestashopheaven/build-coding-standard`](packages/coding-standard) | A module or package needs its `.php-cs-fixer.dist.php`, `tests/phpstan/phpstan.neon`, `phpcs.xml.dist` or `phpunit.xml.dist`: include the shared base and add only its own exceptions. |
| [`prestashopheaven/build-typescript`](packages/typescript) | A module has JavaScript or TypeScript in `_dev/`: extend the tsconfig base, include the PrestaShop typings and call the shared build, ESLint and Vitest configs. |

Both are build packages: `require-dev` in a module, never in its zip. The exact lines a module needs are in each
package's README.

(`ps-module mono packages` lists them; the package index in the workspace has every class.)

## Work on it

```
composer install
composer check                 # ps-module mono check: the definition of done of every package
ps-module mono package <dir> "<description>" "<use when>" <keywords>   # a new package
```

Changes are made here, never in a split repo.

## Release

1. Write the changes under `## X.Y.Z` in `CHANGELOG.md` (and in the `CHANGELOG.md` of each changed package).
2. `ps-module mono set-version X.Y.Z` (root version, branch aliases, internal constraints).
3. Commit `Release X.Y.Z - <notes>` on `main` and push.

The release workflow checks everything, tags `vX.Y.Z`, makes the GitHub release, splits every package to its repo with
the same tag and updates Packagist. Never tag by hand.

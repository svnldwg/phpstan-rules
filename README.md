# phpstan-rules

Provides additional rules for [`phpstan/phpstan`](https://github.com/phpstan/phpstan).

This package is still in development, please report any issues or bugs.

![CI Status](https://github.com/svnldwg/phpstan-rules/workflows/Tests%20&%20Code%20Check/badge.svg)
![GitHub last commit](https://img.shields.io/github/last-commit/svnldwg/phpstan-rules)
![PHPStan](https://img.shields.io/badge/PHPStan-Level%208-brightgreen.svg?style=flat)
![PHP Versions](https://img.shields.io/badge/php-%3E%3D%207.2-8892BF.svg)
[![Downloads](https://img.shields.io/packagist/dt/svnldwg/phpstan-rules.svg?v1)](https://packagist.org/packages/svnldwg/phpstan-rules)

## Installation

Run

```sh
$ composer require --dev svnldwg/phpstan-rules
```

## Usage

The [rules](https://github.com/svnldwg/phpstan-rules#rules) provided are included in [`rules.neon`](rules.neon).

When you are using [`phpstan/extension-installer`](https://github.com/phpstan/extension-installer), `rules.neon` will be automatically included.

Otherwise you need to include `rules.neon` in your `phpstan.neon`:

```neon
includes:
	- vendor/svnldwg/phpstan-rules/rules.neon
```

## Rules

This package provides the following rules for use with [`phpstan/phpstan`](https://github.com/phpstan/phpstan):

* [`Ergebnis\PHPStan\Rules\Classes\FinalRule`](https://github.com/svnldwg/phpstan-rules#classesfinalrule)

### `ImmutableObjectRule`

This rule reports an error when a class or class property tagged as immutable is mutable. This can be used for example to ensure that value objects are always immutable.

:bulb: Classes or properties can be tagged as immutable by adding the annotation `@psalm-immutable` or `@immutable` to the phpdoc.

:bulb: The immutability rule also asserts that immutable properties are not mutated in child classes

## License

This package is licensed using the MIT License.

Please have a look at [`LICENSE.md`](LICENSE.md).

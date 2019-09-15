# Auto Package Discovery

[![Build Status](https://travis-ci.com/bitpressio/auto-package-discovery.svg?branch=master)](https://travis-ci.com/bitpressio/auto-package-discovery)

A CLI tool to generate Laravel's [package discovery](https://laravel.com/docs/packages#package-discovery) composer configuration.

## Installation

You can install this CLI tool globally and run it from the root of a Laravel package:

```shell
composer global require bitpress/auto-package-discovery
```

## Usage

If you install the package globally you can use the CLI as follows on your Laravel package source code:

```shell
cd path/to/laravel/package
laravel-discover

# Or specify a path
laravel-discover path/to/laravel/package
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

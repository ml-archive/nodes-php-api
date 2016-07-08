## API

A "mobile friendly" API package made on-top of the popular [Dingo API](https://github.com/dingo/api) package.

[![Total downloads](https://img.shields.io/packagist/dt/nodes/api.svg)](https://packagist.org/packages/nodes/api)
[![Monthly downloads](https://img.shields.io/packagist/dm/nodes/api.svg)](https://packagist.org/packages/nodes/api)
[![Latest release](https://img.shields.io/packagist/v/nodes/api.svg)](https://packagist.org/packages/nodes/api)
[![Open issues](https://img.shields.io/github/issues/nodes-php/api.svg)](https://github.com/nodes-php/api/issues)
[![License](https://img.shields.io/packagist/l/nodes/api.svg)](https://packagist.org/packages/nodes/api)
[![Star repository on GitHub](https://img.shields.io/github/stars/nodes-php/api.svg?style=social&label=Star)](https://github.com/nodes-php/api/stargazers)
[![Watch repository on GitHub](https://img.shields.io/github/watchers/nodes-php/api.svg?style=social&label=Watch)](https://github.com/nodes-php/api/watchers)
[![Fork repository on GitHub](https://img.shields.io/github/forks/nodes-php/api.svg?style=social&label=Fork)](https://github.com/nodes-php/api/network)

## 📝 Introduction

Before this package we used the awesome and popular [Dingo API](https://github.com/dingo/api) package, but as a company who create **a lot** of native iOS / Android apps,
[Dingo](http://github.com/dingo/api) was lacking a few things here and there.

This package is in some way a more "mobile friendly" version of [Dingo](https://github.com/dingo/api). It is build on-top of [Dingo](https://github.com/dingo/api) so all the goodies
that [Dingo](https://github.com/dingo/api) comes with out-of-the-box is also available here.

We simply just added extra functionality and made it more flexible.

## 📦 Installation

To install this package you will need:

* Laravel 5.1+
* PHP 5.5.9+

You must then modify your `composer.json` file and run `composer update` to include the latest version of the package in your project.

```json
"require": {
    "nodes/api": "^1.0"
}
```

Or you can run the composer require command from your terminal.

```bash
composer require nodes/api:^1.0
```

## 🔧 Setup

Setup service providers in `config/app.php`

```php
Nodes\Api\ServiceProvider::class,
```

Setup alias in `config/app.php`

```php
'API' => Nodes\Api\Support\Facades\API::class,
'APIRoute' => Nodes\Api\Support\Facades\Route::class
```

Publish config files

```bash
php artisan vendor:publish --provider="Nodes\Api\ServiceProvider"
```

If you want to overwrite any existing config files use the `--force` parameter

```bash
php artisan vendor:publish --provider="Nodes\Api\ServiceProvider" --force
```

#### Bypass Laravel's CSRF tokens

Laravel comes with a built-in CSRF token system, which is by default hooked into all `POST` requests. This gives us a bit of a problem
since API requests won't contain the required CSRF token that Laravel expects. Therefore we need to _whitelist_ all requests hitting our API. 

This can be done by modifying the following file `app/Http/Middleware/VerifyCsrfToken.php` and add `api/*` to the `$except` array:

```php
protected $except = [
    'api/*',
];
```

## ⚙ Usage

Please refer to our extensive [Wiki dokumentation](https://github.com/nodes-php/api/wiki) for more infromation

## 🏆 Credits

This package is developed and maintained by the PHP team at [Nodes](http://nodesagency.com)

[![Follow Nodes PHP on Twitter](https://img.shields.io/twitter/follow/nodesphp.svg?style=social)](https://twitter.com/nodesphp) [![Tweet Nodes PHP](https://img.shields.io/twitter/url/http/nodesphp.svg?style=social)](https://twitter.com/nodesphp)

## 📄 License

This package is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)

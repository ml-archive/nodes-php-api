## API

A "mobile friendly" API package made on-top of the popular [Dingo API](https://github.com/dingo/api) package.

[![Total downloads](https://img.shields.io/packagist/dt/nodes/api.svg)](https://packagist.org/packages/nodes/api)
[![Monthly downloads](https://img.shields.io/packagist/dm/nodes/api.svg)](https://packagist.org/packages/nodes/api)
[![Latest release](https://img.shields.io/packagist/v/nodes/api.svg)](https://packagist.org/packages/nodes/api)
[![Open issues](https://img.shields.io/github/issues/nodes-php/backend.svg)](https://github.com/nodes-php/backend/issues)
[![License](https://img.shields.io/packagist/l/nodes/api.svg)](https://packagist.org/packages/nodes/api)
[![Star repository on GitHub](https://img.shields.io/github/stars/nodes-php/backend.svg?style=social&label=Star)](https://github.com/nodes-php/backend/stargazers)
[![Watch repository on GitHub](https://img.shields.io/github/watchers/nodes-php/backend.svg?style=social&label=Watch)](https://github.com/nodes-php/backend/watchers)
[![Fork repository on GitHub](https://img.shields.io/github/forks/nodes-php/backend.svg?style=social&label=Fork)](https://github.com/nodes-php/backend/network)

## 📝 Introduction
Before this package we used the awesome and popular [Dingo API](https://github.com/dingo/api) package, but as a company who create **a lot** of native iOS / Android apps,
[Dingo](http://github.com/dingo/api) was lacking a few things here and there.

This package is in some way a more "mobile friendly" version of [Dingo](https://github.com/dingo/api). It is build on-top of [Dingo](https://github.com/dingo/api) so all the goodies
that [Dingo](https://github.com/dingo/api) contains is also availble here.

We simply just added extra functionality and made it more flexible.

## 📦 Installation

To install this package you will need:

* Laravel 5.1+
* PHP 5.5.9+

You must then modify your `composer.json` file and run `composer update` to include the latest version of the package in your project.

```
"require": {
    "nodes/api": "^1.0"
}
```

Or you can run the composer require command from your terminal.

```
composer require nodes/api
```

## 🔧 Setup

Setup service providers in `config/app.php`

```
Nodes\Api\ServiceProvider::class,
```

Setup alias in `config/app.php`

```
'API' => Nodes\Api\Support\Facades\Api::class,
'APIRoute' => Nodes\Api\Support\Facades\Route::class
```

Publish config files

```
php artisan vendor:publish --provider="Nodes\Api\ServiceProvider"
```

## ⚙ Usage

Please refer to our extensive [Wiki dokumentation](https://github.com/nodes-php/api/wiki) for more infromation

## 🏆 Credits

This package is developed and maintained by the PHP team at [Nodes](http://nodesagency.com)

[![Follow Nodes PHP on Twitter](https://img.shields.io/twitter/follow/nodesphp.svg?style=social)](https://twitter.com/nodesphp) [![Tweet Nodes PHP](https://img.shields.io/twitter/url/http/nodesphp.svg?style=social)](https://twitter.com/nodesphp)

## 📄 License

This package is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
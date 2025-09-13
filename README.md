# Laravel Settings

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rdcstarr/laravel-settings.svg?style=flat-square)](https://packagist.org/packages/rdcstarr/laravel-settings)
[![Tests](https://img.shields.io/github/actions/workflow/status/rdcstarr/laravel-settings/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/rdcstarr/laravel-settings/actions)
[![Code Style](https://img.shields.io/github/actions/workflow/status/rdcstarr/laravel-settings/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/rdcstarr/laravel-settings/actions)
[![Downloads](https://img.shields.io/packagist/dt/rdcstarr/laravel-settings.svg?style=flat-square)](https://packagist.org/packages/rdcstarr/laravel-settings)

> Elegant package for managing **application settings** in Laravel — with groups, caching, and multiple access methods.

---

## ✨ Features

- 🔧 **Groups** – organize settings logically
- ⚡ **Cache** – built-in cache layer for speed
- 🎯 **Access** – helper, facade, or DI
- 📦 **Batch ops** – set multiple values at once
- 🔄 **Fluent API** – method chaining for clean code

---

## 📦 Installation

```bash
composer require rdcstarr/laravel-settings
```

Publish & migrate:

```bash
php artisan vendor:publish --tag="settings-migrations"
php artisan migrate
```

## 🚀 Quick Start

```php
// Set & get values
settings(['app_name' => 'My App']);
$theme = settings('ui_theme', 'light');

// Work with groups
settings()->group('mail')->set('driver', 'smtp');
$mailDriver = settings()->group('mail')->get('driver');
```

## 🔑 Usage

### Set Values
```php
// single
settings(['language' => 'english']);

// batch in group
settings()->group('mail')->set([
    'driver' => 'smtp',
    'host' => 'smtp.example.com',
    'port' => 587,
    'encryption' => 'tls',
]);
```

### Get Values
```php
$theme = settings('ui_theme', 'light');       // with default
$mail  = settings()->group('mail')->all();    // group values
```

### Facade
```php
use Rdcstarr\Settings\Facades\Settings;

Settings::set('app_name', 'My App');
$driver = Settings::group('mail')->get('driver', 'smtp');
```

### Extra Operations
```php
settings()->has('app_name');       // check existence
settings()->forget('old_setting'); // delete
settings()->flushCache();          // clear cache
```
---
## 🎨 Blade Directives
```php
{{-- Simple --}}
@settings('app_name', 'Default')

{{-- Grouped --}}
@settingsGroup('mail', 'driver', 'smtp')

{{-- Conditional --}}
@hasSettings('maintenance_mode')
    <div class="alert">Maintenance mode active</div>
@endhasSettings
```

## 💡 Examples
```php
// User preferences
settings()->group("user_".auth()->id())->set([
    'theme' => 'dark',
    'language' => 'en',
]);

// Feature flags
if (settings()->group('features')->get('new_dashboard', false))
{
    // Enable feature
}
```

## 🧪 Testing
```bash
composer test
```

## 📖 Resources
 - [Changelog](CHANGELOG.md) for more information on what has changed recently.
 - [Contributing](CONTRIBUTING.md) for details.
 - [Security Vulnerabilities](../../security/policy) on how to report security vulnerabilities.

## 👥 Credits
 - [Rdcstarr](https://github.com/rdcstarr)

## 📜 License
 - [License](LICENSE.md) for more information.

# Laravel Settings

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rdcstarr/laravel-settings.svg?style=flat-square)](https://packagist.org/packages/rdcstarr/laravel-settings)
[![Tests](https://img.shields.io/github/actions/workflow/status/rdcstarr/laravel-settings/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/rdcstarr/laravel-settings/actions)
[![Code Style](https://img.shields.io/github/actions/workflow/status/rdcstarr/laravel-settings/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/rdcstarr/laravel-settings/actions)
[![Downloads](https://img.shields.io/packagist/dt/rdcstarr/laravel-settings.svg?style=flat-square)](https://packagist.org/packages/rdcstarr/laravel-settings)

> Elegant package for managing **application settings** in Laravel — with caching and multiple access methods.

---

## ✨ Features

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

## 🔑 Usage

### Set Values
```php
// single
settings()->set('app.name', 'My App');

// batch
settings()->set([
    'mail.driver' => 'smtp',
    'mail.host' => 'smtp.example.com',
    'mail.port' => 587,
    'mail.encryption' => 'tls',
]);
```

### Get Values
```php
$theme = settings('app.theme', 'light');      // with default
$all   = settings()->all();                   // all values
```

### Facade
```php
use Rdcstarr\Settings\Facades\Settings;

Settings::set('app.name', 'My App');
$driver = Settings::get('mail.driver', 'smtp');
```

### Extra Operations
```php
settings()->has('app.name');       // check existence
settings()->forget('old.setting'); // delete
settings()->flushCache();          // clear cache
```
---
## 🎨 Blade Directives
```php
{{-- Simple --}}
@settings('app_name', 'Default')

{{-- Conditional --}}
@hasSettings('maintenance_mode')
    <div class="alert">Maintenance mode active</div>
@endhasSettings
```

## 💡 Examples
```php
// User preferences
settings([
    'user_' . auth()->id() . '_theme' => 'dark',
    'user_' . auth()->id() . '_language' => 'en',
]);

// Feature flags
if (settings('features_new_dashboard', false))
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

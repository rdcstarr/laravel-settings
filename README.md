# Laravel Settings

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rdcstarr/laravel-settings.svg?style=flat-square)](https://packagist.org/packages/rdcstarr/laravel-settings)
[![Tests](https://img.shields.io/github/actions/workflow/status/rdcstarr/laravel-settings/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/rdcstarr/laravel-settings/actions)
[![Code Style](https://img.shields.io/github/actions/workflow/status/rdcstarr/laravel-settings/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/rdcstarr/laravel-settings/actions)
[![Downloads](https://img.shields.io/packagist/dt/rdcstarr/laravel-settings.svg?style=flat-square)](https://packagist.org/packages/rdcstarr/laravel-settings)

> Elegant package for managing **application settings** in Laravel — with caching and multiple access methods.

## ✨ Features

- ⚡ **Cache** – built-in cache layer for speed
- 🎯 **Access** – helper, facade, or DI
- 📦 **Batch ops** – set multiple values at once
- 🔄 **Fluent API** – method chaining for clean code
- 🗂️ **Groups** – organize settings by logical groups (e.g., `admin`, `user`, `tenant`) for scoped configuration

## 📦 Installation

Install the package via Composer:

```bash
composer require rdcstarr/laravel-settings
```

1. **Publish migrations files** (optional):
   ```bash
   php artisan vendor:publish --provider="Rdcstarr\Settings\SettingsServiceProvider"
   ```

2. **Migrate** (required):
   ```bash
   php artisan migrate
   ```

## 🛠️ Artisan Commands

The package provides dedicated Artisan commands for managing settings directly from the command line:

#### Clear cache
```bash
php artisan settings:clear-cache [--group=] [--force]
```

#### Delete settings
```bash
php artisan settings:delete [key] [--group=] [--force]
```

#### Get setting
```bash
php artisan settings:get [key] [--group=]
```

#### List all groups
```bash
php artisan settings:groups
```

#### List all settings for specific group
```bash
php artisan settings:list [--group=]
```

#### Set setting
```bash
php artisan settings:set [key] [value] [--group=]
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

// or use setMany for cleaner syntax
settings()->setMany([
    'app.theme' => 'dark',
    'app.language' => 'en',
    'app.timezone' => 'UTC',
]);
```

### Get Values
```php
$theme = settings('app.theme', 'light'); // with default
$all   = settings()->all();              // all values

// get multiple values at once
$config = settings()->getMany(['app.theme', 'app.language', 'app.timezone']);
// returns: ['app.theme' => 'dark', 'app.language' => 'en', 'app.timezone' => 'UTC']
```

### Working with Groups
```php
// set values in specific group
settings()->group('admin')->set('site_name', 'My Admin Panel');
settings()->group('user')->set('theme', 'dark');

// get values from specific group
$siteName = settings()->group('admin')->get('site_name', 'Default Site');
$userTheme = settings()->group('user')->get('theme', 'light');

// get all values from a group
$adminSettings = settings()->group('admin')->all();
```

### Facade
```php
use Rdcstarr\Settings\Facades\Settings;

Settings::set('app.name', 'My App');
$driver = Settings::get('mail.driver', 'smtp');

// working with groups via facade
Settings::group('admin')->set('dashboard_style', 'modern');
$style = Settings::group('admin')->get('dashboard_style', 'classic');
```

### Extra Operations
```php
settings()->has('app.name');       // check existence
settings()->forget('old.setting'); // delete
settings()->flushCache();          // clear cache

// group-specific operations
settings()->group('admin')->has('site_name');     // check in specific group
settings()->group('admin')->forget('old_config'); // delete from specific group
settings()->flushAllCache();                      // clear all groups cache
```
---
## 🎨 Blade Directives
```php
{{-- Simple settings --}}
@settings('app_name', 'Default')

{{-- Settings from specific group --}}
@settingsForGroup('admin', 'site_name', 'My Site')
@settingsForGroup('user', 'theme', 'light')

{{-- Conditional checks --}}
@hasSettings('maintenance_mode')
    <div class="alert">Maintenance mode active</div>
@endhasSettings

{{-- Conditional checks for specific group --}}
@hasSettingsForGroup('admin', 'debug_mode')
    <div class="debug-info">Debug mode enabled</div>
@endhasSettingsForGroup

{{-- More examples --}}
@settingsForGroup('mail', 'from_name', 'Laravel App')
@settingsForGroup('social', 'twitter_handle')
```

## 💡 Examples
```php
// User preferences with groups
settings()->group('user_' . auth()->id())->setMany([
    'theme' => 'dark',
    'language' => 'en',
    'timezone' => 'Europe/London',
]);

// Admin configuration
settings()->group('admin')->setMany([
    'site_name' => 'My Application',
    'maintenance_mode' => false,
    'debug_enabled' => true,
]);

// Feature flags
if (settings()->group('features')->get('new_dashboard', false)) {
    // Enable new dashboard feature
}

// Get user preferences in one call
$userPrefs = settings()->group('user_' . auth()->id())
    ->getMany(['theme', 'language', 'timezone']);
```

## 🧪 Testing
```bash
composer test
```

## 📖 Resources
 - [Changelog](CHANGELOG.md) for more information on what has changed recently. ✍️

## 👥 Credits
 - [Rdcstarr](https://github.com/rdcstarr) 🙌

## 📜 License
 - [License](LICENSE.md) for more information. ⚖️

# Laravel Settings

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rdcstarr/laravel-settings.svg?style=flat-square)](https://packagist.org/packages/rdcstarr/laravel-settings)
[![Tests](https://img.shields.io/github/actions/workflow/status/rdcstarr/laravel-settings/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/rdcstarr/laravel-settings/actions)
[![Code Style](https://img.shields.io/github/actions/workflow/status/rdcstarr/laravel-settings/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/rdcstarr/laravel-settings/actions)
[![Downloads](https://img.shields.io/packagist/dt/rdcstarr/laravel-settings.svg?style=flat-square)](https://packagist.org/packages/rdcstarr/laravel-settings)

> Simple settings management for Laravel — because configuration should be easy.

## ✨ Features

-   ⚡ **Cache built-in** – automatic caching for blazing-fast reads
-   🔄 **Smart type casting** – automatic conversion between strings, integers, booleans, arrays, and JSON
-   📦 **Batch operations** – set multiple values at once with `setMany()`
-   🛠️ **Artisan commands** – manage settings directly from the terminal
-   💯 **No exceptions** – returns `false` on failure, never crashes your app

## 📦 Installation

Install the package via Composer:

```bash
composer require rdcstarr/laravel-settings
```

### Automatic Installation (Recommended)

Run the install command to publish and run the migrations:

```bash
php artisan settings:install
```

### Manual Installation

Alternatively, you can install manually:

1. Publish the migrations:

```bash
php artisan vendor:publish --provider="Rdcstarr\Settings\SettingsServiceProvider" --tag="laravel-settings-migrations"
```

2. Run the migrations:

```bash
php artisan migrate
```

## 🛠️ Artisan Commands

Manage your settings directly from the command line:

```bash
# List all settings
php artisan settings:list

# Get a specific setting
php artisan settings:get app.name

# Set a setting
php artisan settings:set app.name "My Application"

# Delete a setting
php artisan settings:delete old.setting [--force]

# Clear cache
php artisan settings:clear-cache [--force]
```

## 🔑 Usage

### Basic Operations

```php
// Get a setting (returns false if not found)
$name = settings()->get('app.name', 'Default Name');

// Set a setting (returns true on success, false if unchanged or failed)
settings()->set('app.name', 'My Application');

// Check if a setting exists
if (settings()->has('app.name')) {
    // Setting exists
}

// Delete a setting (returns true if deleted, false otherwise)
settings()->delete('old.setting');

// Get all settings
$all = settings()->all(); // Collection
```

### Batch Operations

```php
// Set multiple settings at once
settings()->setMany([
    'app.name' => 'My Application',
    'app.theme' => 'dark',
    'app.language' => 'en',
    'mail.driver' => 'smtp',
    'mail.host' => 'smtp.example.com',
]);
```

### Helper Function

```php
// Quick access with helper
$theme = settings('app.theme', 'light');

// Same as
$theme = settings()->get('app.theme', 'light');
```

### Using the Facade

```php
use Rdcstarr\Settings\Facades\Settings;

Settings::set('app.name', 'My App');
$name = Settings::get('app.name', 'Default');
Settings::delete('old.setting');
```

### Cache Management

```php
// Clear the cache (returns true on success)
settings()->flushCache();
```

### Smart Type Casting

The package automatically handles type conversion:

```php
// Integers
settings()->set('max_users', 100);
settings()->get('max_users'); // returns (int) 100

// Booleans
settings()->set('maintenance_mode', true);
settings()->get('maintenance_mode'); // returns (bool) true

// Arrays
settings()->set('config', ['key' => 'value']);
settings()->get('config'); // returns array

// Null values
settings()->set('optional', null);
settings()->get('optional'); // returns null
```

## 💡 Real-World Examples

```php
// Application configuration
settings()->setMany([
    'site.name' => 'My Website',
    'site.description' => 'A wonderful site',
    'site.maintenance_mode' => false,
]);

// Feature flags
if (settings()->get('features.new_dashboard', false)) {
    return view('dashboard.new');
}

// User preferences
settings()->set("user.{$userId}.theme", 'dark');
settings()->set("user.{$userId}.language", 'en');

// Email settings
$mailConfig = [
    'mail.driver' => 'smtp',
    'mail.host' => 'smtp.mailtrap.io',
    'mail.port' => 2525,
    'mail.username' => 'user',
    'mail.password' => 'pass',
];
settings()->setMany($mailConfig);

// API keys and secrets
settings()->set('services.stripe.key', 'sk_test_...');
settings()->set('services.stripe.secret', 'whsec_...');
```

## 🧪 Testing

```bash
composer test
```

## 📖 Resources

-   [Changelog](CHANGELOG.md) for more information on what has changed recently. ✍️

## 👥 Credits

-   [Rdcstarr](https://github.com/rdcstarr) 🙌

## 📜 License

-   [License](LICENSE.md) for more information. ⚖️

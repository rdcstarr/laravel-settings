<?php

namespace Rdcstarr\Settings;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Rdcstarr\Settings\Commands\SettingsCommand;
use Illuminate\Support\Facades\Blade;

class SettingsServiceProvider extends PackageServiceProvider
{
	public function register(): void
	{
		parent::register();

		$this->app->singleton('settings', Settings::class);
	}

	public function boot(): void
	{
		parent::boot();

		// @settings('key', 'default')
		Blade::directive('settings', fn($expression) => "<?php echo e(settings()->get($expression)); ?>");

		// @hasSettings('key')
		Blade::if('hasSettings', fn($key) => settings()->has($key));
	}

	public function configurePackage(Package $package): void
	{
		/*
		 * This class is a Package Service Provider
		 *
		 * More info: https://github.com/spatie/laravel-package-tools
		 */
		$package->name('settings')
			->hasMigration('create_settings_table')
			->hasCommand(SettingsCommand::class);
	}
}

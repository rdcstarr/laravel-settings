<?php

namespace Rdcstarr\Settings;

use Rdcstarr\Settings\Commands\SettingsClearCacheCommand;
use Rdcstarr\Settings\Commands\SettingsDeleteCommand;
use Rdcstarr\Settings\Commands\SettingsGetCommand;
use Rdcstarr\Settings\Commands\SettingsListCommand;
use Rdcstarr\Settings\Commands\SettingsSetCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SettingsServiceProvider extends PackageServiceProvider
{
	public function configurePackage(Package $package): void
	{
		/*
		 * This class is a Package Service Provider
		 *
		 * More info: https://github.com/spatie/laravel-package-tools
		 */
		$package->name('settings')
			->hasCommands([
				SettingsListCommand::class,
				SettingsSetCommand::class,
				SettingsGetCommand::class,
				SettingsDeleteCommand::class,
				SettingsClearCacheCommand::class,
			]);
	}

	public function register(): void
	{
		parent::register();

		$this->app->singleton('settings', Settings::class);
	}

	public function boot(): void
	{
		parent::boot();

		// Load migrations
		if (app()->runningInConsole())
		{
			$this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

			// Publish migrations
			$this->publishes([
				__DIR__ . '/../database/migrations' => database_path('migrations'),
			], 'migrations');
		}
	}
}

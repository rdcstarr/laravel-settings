<?php

namespace Rdcstarr\Settings;

use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Rdcstarr\Settings\Commands\SettingsCommand;

class SettingsServiceProvider extends PackageServiceProvider
{
	public function register(): void
	{
		parent::register();

		$this->app->singleton('settings', fn($app) => new Settings());
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

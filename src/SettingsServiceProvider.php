<?php

namespace Rdcstarr\Settings;

use Rdcstarr\Settings\Commands\SettingsClearCacheCommand;
use Rdcstarr\Settings\Commands\SettingsDeleteCommand;
use Rdcstarr\Settings\Commands\SettingsGetCommand;
use Rdcstarr\Settings\Commands\SettingsListCommand;
use Rdcstarr\Settings\Commands\SettingsSetCommand;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SettingsServiceProvider extends PackageServiceProvider
{
	/*
	 * This class is a Package Service Provider
	 *
	 * More info: https://github.com/spatie/laravel-package-tools
	 */
	public function configurePackage(Package $package): void
	{
		$package->name('laravel-settings')
			->discoversMigrations()
			->runsMigrations()
			->hasCommands([
				SettingsListCommand::class,
				SettingsSetCommand::class,
				SettingsGetCommand::class,
				SettingsDeleteCommand::class,
				SettingsClearCacheCommand::class,
			])
			->hasInstallCommand(function (InstallCommand $command)
			{
				$command
					->publishMigrations()
					->askToRunMigrations();
			});
	}

	public function register(): void
	{
		parent::register();

		$this->app->singleton('settings', SettingsService::class);
	}
}

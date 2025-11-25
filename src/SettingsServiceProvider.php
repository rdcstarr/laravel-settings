<?php

namespace Rdcstarr\Settings;

use Rdcstarr\Settings\Commands\InstallSettingsCommand;
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
		$package->name('laravel-settings')
			->hasMigration('create_settings_table')
			->hasCommands([
				InstallSettingsCommand::class,
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

		$this->app->singleton('settings', SettingsService::class);
	}
}

<?php

namespace Rdcstarr\Settings;

use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Rdcstarr\Settings\Commands\SettingsCommand;

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
			->hasConfigFile()
			->hasMigration('create_settings_table')
			->hasCommand(SettingsCommand::class)
			->hasInstallCommand(function (InstallCommand $command)
			{
				$command
					->startWith(function (InstallCommand $command)
					{
						$command->info('🤗 Hello, and welcome to [rdcstarr/laravel-settings]!');
					})
					->publishConfigFile()
					->publishMigrations()
					->askToRunMigrations()
					->askToStarRepoOnGitHub('rdcstarr/laravel-settings')
					->endWith(function (InstallCommand $command)
					{
						$command->info('Have a great day!');
					});
			})
		;
	}
}

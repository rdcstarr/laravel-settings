<?php

namespace Rdcstarr\Settings;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Rdcstarr\Settings\Commands\SettingsListCommand;
use Rdcstarr\Settings\Commands\SettingsSetCommand;
use Rdcstarr\Settings\Commands\SettingsGetCommand;
use Rdcstarr\Settings\Commands\SettingsDeleteCommand;
use Rdcstarr\Settings\Commands\SettingsClearCacheCommand;
use Rdcstarr\Settings\Commands\SettingsGroupsCommand;
use Illuminate\Support\Facades\Blade;

class SettingsServiceProvider extends PackageServiceProvider
{
	public function register(): void
	{
		parent::register();

		$this->app->singleton('settings', SettingsManager::class);
	}

	public function boot(): void
	{
		parent::boot();

		// @settings('key', 'default')
		Blade::directive('settings', fn($expression) => "<?php echo e(settings()->get($expression)); ?>");

		// @settingsForGroup('group', 'key', 'default')
		Blade::directive('settingsForGroup', function ($expression)
		{
			[$group, $key, $default] = array_pad(explode(',', $expression, 3), 3, null);
			$group                   = trim($group);
			$key                     = $key ? trim($key) : "''";
			$default                 = $default ? trim($default) : 'null';

			return "<?php echo e(settings()->group({$group})->get({$key}, {$default})); ?>";
		});

		// @hasSettings('key')
		Blade::if('hasSettings', fn($key) => settings()->has($key));

		// @hasSettingsForGroup('group', 'key')
		Blade::if('hasSettingsForGroup', fn($group, $key) => settings()->group($group)->has($key));
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
			->hasCommands([
				SettingsListCommand::class,
				SettingsSetCommand::class,
				SettingsGetCommand::class,
				SettingsDeleteCommand::class,
				SettingsClearCacheCommand::class,
				SettingsGroupsCommand::class,
			]);
	}
}

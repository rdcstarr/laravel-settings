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

		$this->app->singleton('settings', fn($app) => new Settings());
	}

	public function boot(): void
	{
		parent::boot();

		// @settings('key', 'default') -> echoes a value from the default group
		Blade::directive('settings', fn($expression) => "<?php echo e(settings()->get($expression)); ?>");

		// @settingsGroup('group', 'key', 'default') -> echoes a value from a specific group
		Blade::directive('settingsGroup', function ($expression)
		{
			$parts = explode(',', $expression, 2);
			$group = trim($parts[0] ?? '');
			$args  = $parts[1] ?? '';
			return "<?php echo e(settings()->group($group)->get($args)); ?>";
		});

		// @hasSettings('key') or @hasSettings('key', 'group')
		Blade::if('hasSettings', function ($key, $group = null)
		{
			$settings = settings();
			if ($group)
			{
				$settings->group($group);
			}
			return $settings->has($key);
		});
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

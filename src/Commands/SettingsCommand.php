<?php

namespace Rdcstarr\Settings\Commands;

use Illuminate\Console\Command;
use Rdcstarr\Settings\Settings;

class SettingsCommand extends Command
{
	protected $signature = 'settings
		{action : The action to perform (list, set, get, delete, clear-cache)}
		{--group= : The settings group to work with}
		{--key= : The setting key}
		{--value= : The setting value}
		{--default= : Default value for get operation}
	';

	protected $description = 'Manage application settings';

	public function handle(Settings $settings): int
	{
		$action  = $this->argument('action');
		$group   = $this->option('group');
		$key     = $this->option('key');
		$value   = $this->option('value');
		$default = $this->option('default');

		// Set group if provided
		if ($group)
		{
			$settings = $settings->group($group);
		}

		return match ($action)
		{
			'list', 'all' => $this->listSettings($settings, $group),
			'set', 'add' => $this->setSetting($settings, $key, $value, $group),
			'get' => $this->getSetting($settings, $key, $default, $group),
			'delete', 'remove', 'forget' => $this->deleteSetting($settings, $key, $group),
			'clear-cache', 'flush' => $this->clearCache($settings),
			default => $this->showHelp(),
		};
	}

	protected function listSettings(Settings $settings, ?string $group): int
	{
		$allSettings = $settings->all();

		if ($allSettings->isEmpty())
		{
			$groupText = $group ? " in group '{$group}'" : '';
			$this->info("No settings found{$groupText}.");
			return self::SUCCESS;
		}

		$groupText = $group ? " (Group: {$group})" : ' (Default Group)';
		$this->info("Settings{$groupText}:");
		$this->newLine();

		$tableData = $allSettings->map(function ($value, $key)
		{
			return [
				'Key'   => $key,
				'Value' => is_array($value) || is_object($value) ? json_encode($value) : (string) $value,
				'Type'  => gettype($value),
			];
		})->values()->toArray();

		$this->table(['Key', 'Value', 'Type'], $tableData);

		return self::SUCCESS;
	}

	protected function setSetting(Settings $settings, ?string $key, ?string $value, ?string $group): int
	{
		if (!$key)
		{
			$key = $this->ask('Enter setting key');
		}

		if (!$key)
		{
			$this->error('Setting key is required.');
			return self::FAILURE;
		}

		if ($value === null)
		{
			$value = $this->ask('Enter setting value');
		}

		// Try to decode JSON if it looks like JSON
		if ($this->isJson($value))
		{
			$value = json_decode($value, true);
		}

		if ($settings->set($key, $value))
		{
			$groupText = $group ? " in group '{$group}'" : '';
			$this->info("Setting '{$key}' has been set{$groupText}.");
			return self::SUCCESS;
		}

		$this->error('Failed to set setting.');
		return self::FAILURE;
	}

	protected function getSetting(Settings $settings, ?string $key, ?string $default, ?string $group): int
	{
		if (!$key)
		{
			$key = $this->ask('Enter setting key');
		}

		if (!$key)
		{
			$this->error('Setting key is required.');
			return self::FAILURE;
		}

		$value     = $settings->get($key, $default);
		$groupText = $group ? " (group: {$group})" : '';

		if ($value === null && $default === null)
		{
			$this->warn("Setting '{$key}' not found{$groupText}.");
			return self::SUCCESS;
		}

		$displayValue = is_array($value) || is_object($value) ? json_encode($value, JSON_PRETTY_PRINT) : $value;

		$this->info("Setting '{$key}'{$groupText}:");
		$this->line($displayValue);

		return self::SUCCESS;
	}

	protected function deleteSetting(Settings $settings, ?string $key, ?string $group): int
	{
		if (!$key)
		{
			$key = $this->ask('Enter setting key to delete');
		}

		if (!$key)
		{
			$this->error('Setting key is required.');
			return self::FAILURE;
		}

		if (!$settings->has($key))
		{
			$groupText = $group ? " in group '{$group}'" : '';
			$this->warn("Setting '{$key}' does not exist{$groupText}.");
			return self::SUCCESS;
		}

		if ($this->confirm("Are you sure you want to delete setting '{$key}'?", false))
		{
			if ($settings->forget($key))
			{
				$groupText = $group ? " from group '{$group}'" : '';
				$this->info("Setting '{$key}' has been deleted{$groupText}.");
				return self::SUCCESS;
			}

			$this->error('Failed to delete setting.');
			return self::FAILURE;
		}

		$this->info('Operation cancelled.');
		return self::SUCCESS;
	}

	protected function clearCache(Settings $settings): int
	{
		if ($this->confirm('Are you sure you want to clear the settings cache?', false))
		{
			if ($settings->flushCache())
			{
				$this->info('Settings cache has been cleared.');
				return self::SUCCESS;
			}

			$this->error('Failed to clear settings cache.');
			return self::FAILURE;
		}

		$this->info('Operation cancelled.');
		return self::SUCCESS;
	}

	protected function showHelp(): int
	{
		$this->error('Invalid action specified.');
		$this->newLine();

		$this->info('Available actions:');
		$this->line('  <fg=green>list/all</fg=green>        - List all settings');
		$this->line('  <fg=green>set/add</fg=green>         - Set a setting value');
		$this->line('  <fg=green>get</fg=green>             - Get a setting value');
		$this->line('  <fg=green>delete/remove</fg=green>   - Delete a setting');
		$this->line('  <fg=green>clear-cache</fg=green>     - Clear settings cache');

		$this->newLine();
		$this->info('Examples:');
		$this->line('  <fg=yellow>php artisan settings list</fg=yellow>');
		$this->line('  <fg=yellow>php artisan settings list --group=mail</fg=yellow>');
		$this->line('  <fg=yellow>php artisan settings set --key=app_name --value="My App"</fg=yellow>');
		$this->line('  <fg=yellow>php artisan settings set --group=mail --key=driver --value=smtp</fg=yellow>');
		$this->line('  <fg=yellow>php artisan settings get --key=app_name --default="Default App"</fg=yellow>');
		$this->line('  <fg=yellow>php artisan settings delete --key=old_setting</fg=yellow>');
		$this->line('  <fg=yellow>php artisan settings clear-cache</fg=yellow>');

		return self::FAILURE;
	}

	protected function isJson(string $string): bool
	{
		json_decode($string);
		return json_last_error() === JSON_ERROR_NONE;
	}
}

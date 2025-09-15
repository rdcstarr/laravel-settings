<?php

namespace Rdcstarr\Settings\Commands;

use Illuminate\Console\Command;

class SettingsCommand extends Command
{
	protected $signature = 'settings
		{action : The action to perform (list, set, get, delete, clear-cache)}
		{--key= : The setting key}
		{--value= : The setting value}
	';

	protected $description = 'Manage application settings';

	public function handle(): int
	{
		$action = $this->argument('action');
		$key    = $this->option('key');
		$value  = $this->option('value');

		return match ($action)
		{
			'list', 'all' => $this->listSettings(),
			'set', 'add' => $this->setSetting($key, $value),
			'get' => $this->getSetting($key),
			'delete', 'remove', 'forget' => $this->deleteSetting($key),
			'clear-cache', 'flush' => $this->clearCache(),
			default => $this->showHelp(),
		};
	}

	protected function listSettings(): int
	{
		$settings = settings()->all();

		if ($settings->isEmpty())
		{
			$this->info("No settings found.");
			return self::SUCCESS;
		}

		$this->info("Settings:");
		$this->table(
			['Key', 'Value', 'Type'],
			$settings->map(fn($value, $key) => [
				'Key'   => $key,
				'Value' => is_scalar($value) ? (string) $value : json_encode($value),
				'Type'  => gettype($value),
			])->values()->toArray()
		);

		return self::SUCCESS;
	}

	protected function setSetting(?string $key = null, ?string $value = null): int
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

		if (settings()->set($key, $value))
		{
			$this->info("Setting '{$key}' has been set.");
			return self::SUCCESS;
		}

		$this->error('Failed to set setting.');
		return self::FAILURE;
	}

	protected function getSetting(?string $key = null): int
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

		if (!settings()->has($key))
		{
			$this->warn("Setting '{$key}' not found.");
			return self::SUCCESS;
		}

		$this->info("Setting '{$key}':");
		$this->line(settings()->get($key));

		return self::SUCCESS;
	}

	protected function deleteSetting(?string $key = null): int
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

		if (!settings()->has($key))
		{
			$this->warn("Setting '{$key}' does not exist.");
			return self::SUCCESS;
		}

		if ($this->confirm("Are you sure you want to delete setting '{$key}'?", true))
		{
			if (settings()->forget($key))
			{
				$this->info("Setting '{$key}' has been deleted.");
				return self::SUCCESS;
			}

			$this->error('Failed to delete setting.');
			return self::FAILURE;
		}

		$this->info('Operation cancelled.');
		return self::SUCCESS;
	}

	protected function clearCache(): int
	{
		if ($this->confirm('Are you sure you want to clear the settings cache?', true))
		{
			if (settings()->flushCache())
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
		$this->line('  <fg=yellow>php artisan settings set --key=app.name --value="My App"</fg=yellow>');
		$this->line('  <fg=yellow>php artisan settings get --key=app.name --default="Default App"</fg=yellow>');
		$this->line('  <fg=yellow>php artisan settings delete --key=old.setting</fg=yellow>');
		$this->line('  <fg=yellow>php artisan settings clear-cache</fg=yellow>');

		return self::FAILURE;
	}
}

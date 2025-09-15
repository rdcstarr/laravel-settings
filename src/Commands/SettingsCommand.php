<?php

namespace Rdcstarr\Settings\Commands;

use Illuminate\Console\Command;

class SettingsCommand extends Command
{
	protected $signature = 'settings
		{action : The action to perform (list, set, get, delete, clear-cache)}
		{--key= : The setting key}
		{--value= : The setting value}
		{--group= : The setting group (default: default)}
	';

	protected $description = 'Manage application settings with support for groups';

	/**
	 * Execute the console command.
	 *
	 * Handles the main logic for processing different setting actions including
	 * listing, setting, getting, deleting settings, and cache management.
	 *
	 * @return int Command exit code (SUCCESS or FAILURE)
	 */
	public function handle(): int
	{
		$action = $this->argument('action');
		$key    = $this->option('key');
		$value  = $this->option('value');
		$group  = $this->option('group');

		return match ($action)
		{
			'list', 'all' => $this->listSettings($group),
			'set', 'add' => $this->setSetting($key, $value, $group),
			'get' => $this->getSetting($key, $group),
			'delete', 'remove', 'forget' => $this->deleteSetting($key, $group),
			'clear-cache', 'flush' => $this->clearCache($group),
			default => $this->showHelp(),
		};
	}

	/**
	 * Display all settings from the specified group or default group.
	 *
	 * Lists all settings in a formatted table showing key, value, and type.
	 * If no settings exist, displays an informational message.
	 *
	 * @param string|null $group The settings group to list from
	 * @return int Command exit code
	 */
	protected function listSettings(?string $group = null): int
	{
		$settingsInstance = $group ? settings()->group($group) : settings();
		$settings         = $settingsInstance->all();

		if ($settings->isEmpty())
		{
			$groupInfo = $group ? " in group '{$group}'" : '';
			$this->info("No settings found{$groupInfo}.");
			return self::SUCCESS;
		}

		$groupInfo = $group ? " (Group: {$group})" : ' (Group: default)';
		$this->info("Settings{$groupInfo}:");
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

	/**
	 * Set a setting value in the specified group.
	 *
	 * Prompts for key and value if not provided via options.
	 * Creates or updates the setting in the specified group.
	 *
	 * @param string|null $key The setting key
	 * @param string|null $value The setting value
	 * @param string|null $group The settings group
	 * @return int Command exit code
	 */
	protected function setSetting(?string $key = null, ?string $value = null, ?string $group = null): int
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

		if (!$group)
		{
			$group = $this->ask('Enter setting group (leave empty for default)', 'default');
		}

		$settingsInstance = $group && $group !== 'default' ? settings()->group($group) : settings();

		if ($settingsInstance->set($key, $value))
		{
			$groupInfo = $group && $group !== 'default' ? " in group '{$group}'" : '';
			$this->info("Setting '{$key}' has been set{$groupInfo}.");
			return self::SUCCESS;
		}

		$this->error('Failed to set setting.');
		return self::FAILURE;
	}

	/**
	 * Retrieve and display a setting value from the specified group.
	 *
	 * Prompts for the key if not provided via options.
	 * Displays the setting value or a warning if not found.
	 *
	 * @param string|null $key The setting key to retrieve
	 * @param string|null $group The settings group
	 * @return int Command exit code
	 */
	protected function getSetting(?string $key = null, ?string $group = null): int
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

		if (!$group)
		{
			$group = $this->ask('Enter setting group (leave empty for default)', 'default');
		}

		$settingsInstance = $group && $group !== 'default' ? settings()->group($group) : settings();

		if (!$settingsInstance->has($key))
		{
			$groupInfo = $group && $group !== 'default' ? " in group '{$group}'" : '';
			$this->warn("Setting '{$key}' not found{$groupInfo}.");
			return self::SUCCESS;
		}

		$groupInfo = $group && $group !== 'default' ? " (Group: {$group})" : '';
		$this->info("Setting '{$key}'{$groupInfo}:");
		$this->line($settingsInstance->get($key));

		return self::SUCCESS;
	}

	/**
	 * Delete a setting from the specified group.
	 *
	 * Prompts for confirmation before deletion to prevent accidental data loss.
	 * Displays appropriate messages for success, failure, or if setting doesn't exist.
	 *
	 * @param string|null $key The setting key to delete
	 * @param string|null $group The settings group
	 * @return int Command exit code
	 */
	protected function deleteSetting(?string $key = null, ?string $group = null): int
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

		if (!$group)
		{
			$group = $this->ask('Enter setting group (leave empty for default)', 'default');
		}

		$settingsInstance = $group && $group !== 'default' ? settings()->group($group) : settings();

		if (!$settingsInstance->has($key))
		{
			$groupInfo = $group && $group !== 'default' ? " in group '{$group}'" : '';
			$this->warn("Setting '{$key}' does not exist{$groupInfo}.");
			return self::SUCCESS;
		}

		$groupInfo = $group && $group !== 'default' ? " from group '{$group}'" : '';
		if ($this->confirm("Are you sure you want to delete setting '{$key}'{$groupInfo}?", true))
		{
			if ($settingsInstance->forget($key))
			{
				$this->info("Setting '{$key}' has been deleted{$groupInfo}.");
				return self::SUCCESS;
			}

			$this->error('Failed to delete setting.');
			return self::FAILURE;
		}

		$this->info('Operation cancelled.');
		return self::SUCCESS;
	}

	/**
	 * Clear the settings cache for a specific group or all groups.
	 *
	 * Prompts for confirmation before clearing cache to prevent accidental operations.
	 * Can clear cache for a specific group or all groups if no group specified.
	 *
	 * @param string|null $group The settings group to clear cache for
	 * @return int Command exit code
	 */
	protected function clearCache(?string $group = null): int
	{
		$message = $group ? "clear the settings cache for group '{$group}'" : 'clear all settings cache';

		if ($this->confirm("Are you sure you want to {$message}?", true))
		{
			if ($group)
			{
				$settingsInstance = settings()->group($group);
				if ($settingsInstance->flushCache())
				{
					$this->info("Settings cache for group '{$group}' has been cleared.");
					return self::SUCCESS;
				}
			}
			else
			{
				if (settings()->flushAllCache())
				{
					$this->info('All settings cache has been cleared.');
					return self::SUCCESS;
				}
			}

			$this->error('Failed to clear settings cache.');
			return self::FAILURE;
		}

		$this->info('Operation cancelled.');
		return self::SUCCESS;
	}

	/**
	 * Display help information and usage examples.
	 *
	 * Shows available actions, their descriptions, and practical examples
	 * of how to use the command with different options including groups.
	 *
	 * @return int Command exit code (always FAILURE as it indicates invalid usage)
	 */
	protected function showHelp(): int
	{
		$this->error('Invalid action specified.');
		$this->newLine();

		$this->info('Available actions:');
		$this->line('  <fg=green>list/all</fg=green>        - List all settings (optionally filtered by group)');
		$this->line('  <fg=green>set/add</fg=green>         - Set a setting value in a specific group');
		$this->line('  <fg=green>get</fg=green>             - Get a setting value from a specific group');
		$this->line('  <fg=green>delete/remove</fg=green>   - Delete a setting from a specific group');
		$this->line('  <fg=green>clear-cache</fg=green>     - Clear settings cache (specific group or all)');

		$this->newLine();
		$this->info('Examples:');
		$this->line('  <fg=yellow>php artisan settings list</fg=yellow>');
		$this->line('  <fg=yellow>php artisan settings list --group=admin</fg=yellow>');
		$this->line('  <fg=yellow>php artisan settings set --key=app.name --value="My App"</fg=yellow>');
		$this->line('  <fg=yellow>php artisan settings set --key=site_name --value="Admin Panel" --group=admin</fg=yellow>');
		$this->line('  <fg=yellow>php artisan settings get --key=app.name</fg=yellow>');
		$this->line('  <fg=yellow>php artisan settings get --key=site_name --group=admin</fg=yellow>');
		$this->line('  <fg=yellow>php artisan settings delete --key=old.setting</fg=yellow>');
		$this->line('  <fg=yellow>php artisan settings delete --key=old_config --group=admin</fg=yellow>');
		$this->line('  <fg=yellow>php artisan settings clear-cache</fg=yellow>');
		$this->line('  <fg=yellow>php artisan settings clear-cache --group=admin</fg=yellow>');

		return self::FAILURE;
	}
}

<?php

namespace Rdcstarr\Settings\Commands;

use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class SettingsDeleteCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'settings:delete
		{key? : The setting key}
		{--group= : The setting group}
		{--force : Skip confirmation prompt}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Delete a setting from a specific group';

	/**
	 * Execute the console command.
	 */
	public function handle(): int
	{
		$key   = $this->argument('key');
		$group = $this->option('group');
		$force = $this->option('force');

		if (!$key)
		{
			$key = text(
				label: 'Enter setting key to delete',
				placeholder: 'e.g., app.name',
				required: true
			);
		}

		if (!$key)
		{
			$this->error('Setting key is required.');

			return self::FAILURE;
		}

		if (!$group)
		{
			$availableGroups = settings()->getAllGroups();

			$group = ($availableGroups->isNotEmpty()) ? select(
				label: 'Select setting group',
				options: $availableGroups->prepend('default')->unique()->toArray(),
				default: 'default'
			) : text(
				label: 'Enter setting group',
				placeholder: 'Leave empty for default',
				default: 'default'
			);
		}

		$settingsInstance = $group && $group !== 'default' ? settings()->group($group) : settings();

		if (!$settingsInstance->has($key))
		{
			$groupInfo = $group && $group !== 'default' ? " in group '{$group}'" : '';
			$this->warn("Setting '{$key}' does not exist{$groupInfo}.");

			return self::SUCCESS;
		}

		$groupInfo = $group && $group !== 'default' ? " from group '{$group}'" : '';

		if (!$force && !confirm("Are you sure you want to delete setting '{$key}'{$groupInfo}?", false))
		{
			$this->info('Operation cancelled.');

			return self::SUCCESS;
		}

		if ($settingsInstance->forget($key))
		{
			$this->info("Setting '{$key}' has been deleted{$groupInfo}.");

			return self::SUCCESS;
		}

		$this->error('Failed to delete setting.');

		return self::FAILURE;
	}
}

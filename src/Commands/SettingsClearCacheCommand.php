<?php

namespace Rdcstarr\Settings\Commands;

use Illuminate\Console\Command;
use function Laravel\Prompts\confirm;

class SettingsClearCacheCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'settings:clear-cache
		{--group= : The setting group to clear cache for}
		{--force : Skip confirmation prompt}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Clear settings cache for a specific group or all groups';

	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle(): int
	{
		$group = $this->option('group');
		$force = $this->option('force');

		$message = $group ? "clear the settings cache for group '{$group}'" : 'clear all settings cache';

		if (!$force && !confirm("Are you sure you want to {$message}?", false))
		{
			$this->info('Operation cancelled.');
			return self::SUCCESS;
		}

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
}

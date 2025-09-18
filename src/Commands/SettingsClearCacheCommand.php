<?php

namespace Rdcstarr\Settings\Commands;

use Exception;
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
	 */
	public function handle(): void
	{
		$group = $this->option('group');

		if (!$this->option('force') && !$this->confirmClear($group))
		{
			$this->components->warn('Operation cancelled.');
			return;
		}

		$success = match (!!$group)
		{
			true => settings()->group($group)->flushCache(),
			false => settings()->flushAllCache()
		};

		throw_unless($success, new Exception('Failed to clear settings cache.'));

		$this->components->success(
			$group
			? "Settings cache for group '{$group}' has been cleared."
			: 'All settings cache has been cleared.'
		);
	}

	private function confirmClear(?string $group): bool
	{
		$target = $group ? "group '{$group}'" : 'all settings';
		return confirm("Are you sure you want to clear the settings cache for {$target}?", false);
	}
}

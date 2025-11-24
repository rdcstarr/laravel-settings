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
	protected $signature = 'settings:clear-cache {--force : Skip confirmation prompt}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Clear all settings cache';

	/**
	 * Execute the console command.
	 */
	public function handle(): void
	{
		if (!$this->option('force') && !confirm('Are you sure you want to clear the settings cache?', false))
		{
			$this->components->warn('Operation cancelled.');
			return;
		}

		if (!settings()->flushCache())
		{
			$this->components->error('Failed to clear settings cache.');
			return;
		}

		$this->components->success('Settings cache has been cleared.');
	}
}

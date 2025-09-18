<?php

namespace Rdcstarr\Settings\Commands;

use Illuminate\Console\Command;

class SettingsGroupsCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'settings:groups';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'List all available groups and optionally view their settings';

	/**
	 * Execute the console command.
	 */
	public function handle(): void
	{
		$groups = settings()->getAllGroups();

		if ($groups->isEmpty())
		{
			$this->components->warn('No groups found.');
			return;
		}

		$this->components->info('Available groups:');

		$this->table(
			['Group Name'],
			$groups->map(fn($group) => [$group])->toArray()
		);
	}
}

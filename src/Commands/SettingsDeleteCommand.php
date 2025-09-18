<?php

namespace Rdcstarr\Settings\Commands;

use Illuminate\Console\Command;
use InvalidArgumentException;
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
	public function handle(): void
	{
		$key = $this->argument('key')
			?: text('Enter the setting key to delete', 'e.g., app.name', required: true)
			?: throw new InvalidArgumentException('Setting key is required.');

		$availableGroups = settings()->getAllGroups();
		$group           = $this->option('group') ?: (
			$availableGroups->isNotEmpty()
			? select('Select the setting group', $availableGroups->prepend('default')->unique()->toArray(), 'default')
			: text('Enter the setting group', "Leave empty to use 'default'", default: 'default')
		);

		$instance = settings()->group($group);

		if (!$instance->has($key))
		{
			$this->components->warn("The setting '{$key}' does not exist in group '{$group}'.");
			return;
		}

		if (!$this->option('force') && !confirm("Are you sure you want to delete the setting '{$key}' from the group '{$group}'?", false))
		{
			$this->components->error('Operation cancelled.');
			return;
		}

		if (!$instance->forget($key))
		{
			$this->components->error('Failed to delete the setting.');
			return;
		}

		$this->components->success("The setting '{$key}' in group '{$group}' has been deleted.");
	}
}

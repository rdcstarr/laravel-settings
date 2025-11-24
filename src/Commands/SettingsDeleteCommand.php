<?php

namespace Rdcstarr\Settings\Commands;

use Illuminate\Console\Command;
use InvalidArgumentException;
use function Laravel\Prompts\confirm;
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
		{--force : Skip confirmation prompt}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Delete a setting';

	/**
	 * Execute the console command.
	 */
	public function handle(): void
	{
		$key = $this->argument('key')
			?: text('Enter the setting key to delete', 'e.g., app.name', required: true)
			?: throw new InvalidArgumentException('Setting key is required.');

		if (!settings()->has($key))
		{
			$this->components->warn("The setting '{$key}' does not exist.");
			return;
		}

		if (!$this->option('force') && !confirm("Are you sure you want to delete the setting '{$key}'?", false))
		{
			$this->components->error('Operation cancelled.');
			return;
		}

		if (!settings()->delete($key))
		{
			$this->components->error('Failed to delete the setting.');
			return;
		}

		$this->components->success("The setting '{$key}' has been deleted.");
	}
}

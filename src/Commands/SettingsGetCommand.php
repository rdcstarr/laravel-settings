<?php

namespace Rdcstarr\Settings\Commands;

use Illuminate\Console\Command;
use InvalidArgumentException;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class SettingsGetCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'settings:get
		{key? : The setting key}
		{--group= : The setting group}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Get a setting value from a specific group';

	/**
	 * Execute the console command.
	 */
	public function handle(): void
	{
		$key = $this->argument('key')
			?: text('Enter the setting key to get', 'e.g., app.name', required: true)
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

		$this->components->info("Setting '{$key}' from '{$group}':");
		$this->line('  ' . $instance->get($key));
	}
}

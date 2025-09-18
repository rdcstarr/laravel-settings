<?php

namespace Rdcstarr\Settings\Commands;

use Illuminate\Console\Command;
use InvalidArgumentException;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class SettingsSetCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'settings:set
		{key? : The setting key}
		{value? : The setting value}
		{--group= : The setting group}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Set a setting value in a specific group';

	/**
	 * Execute the console command.
	 */
	public function handle(): void
	{
		$key = $this->argument('key')
			?: text('Enter the setting key', 'e.g., app.name', required: true)
			?: throw new InvalidArgumentException('Setting key is required.');

		$value = $this->argument('value')
			?: text('Enter the setting value', 'e.g., My App', required: true)
			?: throw new InvalidArgumentException('Setting value is required.');

		$availableGroups = settings()->getAllGroups();
		$group           = $this->option('group') ?: (
			$availableGroups->isNotEmpty()
			? select('Select the setting group', $availableGroups->prepend('default')->unique()->toArray(), 'default')
			: text('Enter the setting group', "Leave empty to use 'default'", default: 'default')
		);

		$instance = settings()->group($group);

		if ($instance->set($key, $value))
		{
			$this->components->success("Setting '{$key}' from '{$group}' group has been set.");
			return;
		}

		$this->components->error('Failed to set setting.');
	}
}

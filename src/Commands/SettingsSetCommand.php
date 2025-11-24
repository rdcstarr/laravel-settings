<?php

namespace Rdcstarr\Settings\Commands;

use Illuminate\Console\Command;
use InvalidArgumentException;
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
		{value? : The setting value}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Set a setting value';

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

		if (settings()->set($key, $value))
		{
			$this->components->success("Setting '{$key}' has been set.");
			return;
		}

		$this->components->warn('Setting was not updated (value unchanged or operation failed).');
	}
}

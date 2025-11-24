<?php

namespace Rdcstarr\Settings\Commands;

use Illuminate\Console\Command;
use InvalidArgumentException;
use function Laravel\Prompts\text;

class SettingsGetCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'settings:get {key? : The setting key}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Get a setting value';

	/**
	 * Execute the console command.
	 */
	public function handle(): void
	{
		$key = $this->argument('key')
			?: text('Enter the setting key to get', 'e.g., app.name', required: true)
			?: throw new InvalidArgumentException('Setting key is required.');

		if (!settings()->has($key))
		{
			$this->components->warn("The setting '{$key}' does not exist.");
			return;
		}

		$value = settings()->get($key);

		$this->components->info("Setting '{$key}':");
		$this->line('  ' . (is_scalar($value) ? (string) $value : json_encode($value, JSON_PRETTY_PRINT)));
	}
}

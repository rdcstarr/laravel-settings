<?php

namespace Rdcstarr\Settings\Commands;

use Illuminate\Console\Command;

class SettingsListCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'settings:list';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'List all settings';

	/**
	 * Execute the console command.
	 */
	public function handle(): void
	{
		$settings = settings()->all();

		if ($settings->isEmpty())
		{
			$this->components->warn('No settings found.');
			return;
		}

		$this->components->info('All settings:');
		$this->table(
			['Key', 'Value', 'Type'],
			$settings->map(fn($value, $key) => [
				'Key'   => $key,
				'Value' => is_scalar($value) ? (string) $value : json_encode($value),
				'Type'  => gettype($value),
			])->values()->toArray()
		);
	}
}

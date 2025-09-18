<?php

namespace Rdcstarr\Settings\Commands;

use Illuminate\Console\Command;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class SettingsListCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'settings:list {--group= : The setting group to list from}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'List all settings from a specific group';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$availableGroups = settings()->getAllGroups();
		$group           = $this->option('group') ?: (
			$availableGroups->isNotEmpty()
			? select('Select the setting group', $availableGroups->prepend('default')->unique()->toArray(), 'default')
			: text('Enter the setting group', "Leave empty to use 'default'", default: 'default')
		);

		$settings = settings()->group($group)->all();

		if ($settings->isEmpty())
		{
			$this->components->warn("No settings were found in the group '{$group}'.");
			return;
		}

		$this->components->info("List all settings in group '{$group}':");
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

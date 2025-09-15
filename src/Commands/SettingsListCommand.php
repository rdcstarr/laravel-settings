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
	protected $signature = 'settings:list {--group= : The setting group to list from}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'List all settings from a specific group';

	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle(): int
	{
		$group            = $this->option('group');
		$settingsInstance = $group ? settings()->group($group) : settings();
		$settings         = $settingsInstance->all();

		if ($settings->isEmpty())
		{
			$groupInfo = $group ? " in group '{$group}'" : '';
			$this->info("No settings found{$groupInfo}.");
			return self::SUCCESS;
		}

		$groupInfo = $group ? " (Group: {$group})" : ' (Group: default)';
		$this->info("Settings{$groupInfo}:");
		$this->table(
			['Key', 'Value', 'Type'],
			$settings->map(fn($value, $key) => [
				'Key'   => $key,
				'Value' => is_scalar($value) ? (string) $value : json_encode($value),
				'Type'  => gettype($value),
			])->values()->toArray()
		);

		return self::SUCCESS;
	}
}

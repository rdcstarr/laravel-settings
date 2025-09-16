<?php

namespace Rdcstarr\Settings\Commands;

use Illuminate\Console\Command;

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
	public function handle(): int
	{
		$key   = $this->argument('key');
		$value = $this->argument('value');
		$group = $this->option('group');

		if (!$key)
		{
			$key = text(
				label: 'Enter setting key',
				placeholder: 'e.g., app.name',
				required: true
			);
		}

		if (!$key)
		{
			$this->error('Setting key is required.');

			return self::FAILURE;
		}

		if ($value === null)
		{
			$value = text(
				label: 'Enter setting value',
				placeholder: 'Enter the value for this setting'
			);
		}

		if (!$group)
		{
			$availableGroups = settings()->getAllGroups();

			$group = ($availableGroups->isNotEmpty()) ? select(
				label: 'Select setting group',
				options: $availableGroups->prepend('default')->unique()->toArray(),
				default: 'default'
			) : text(
				label: 'Enter setting group',
				placeholder: 'Leave empty for default',
				default: 'default'
			);
		}

		$settingsInstance = $group && $group !== 'default' ? settings()->group($group) : settings();

		if ($settingsInstance->set($key, $value))
		{
			$groupInfo = $group && $group !== 'default' ? " in group '{$group}'" : '';
			$this->info("Setting '{$key}' has been set{$groupInfo}.");

			return self::SUCCESS;
		}

		$this->error('Failed to set setting.');

		return self::FAILURE;
	}
}

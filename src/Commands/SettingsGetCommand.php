<?php

namespace Rdcstarr\Settings\Commands;

use Illuminate\Console\Command;

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
	public function handle(): int
	{
		$key   = $this->argument('key');
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

		if (!$settingsInstance->has($key))
		{
			$groupInfo = $group && $group !== 'default' ? " in group '{$group}'" : '';
			$this->warn("Setting '{$key}' not found{$groupInfo}.");

			return self::SUCCESS;
		}

		$groupInfo = $group && $group !== 'default' ? " (Group: {$group})" : '';
		$this->info("Setting '{$key}'{$groupInfo}:");
		$this->line($settingsInstance->get($key));

		return self::SUCCESS;
	}
}

<?php

namespace Rdcstarr\Settings\Commands;

use Illuminate\Console\Command;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;

class SettingsGroupsCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'settings:groups';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'List all available groups and optionally view their settings';

	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle(): int
	{
		$groups = settings()->getAllGroups();

		if ($groups->isEmpty())
		{
			$this->info('No groups found.');
			return self::SUCCESS;
		}

		$this->info('Available groups:');
		$this->table(
			['Group Name'],
			$groups->map(fn($group) => [$group])->toArray()
		);

		// Ask if user wants to view settings from a specific group
		if (confirm('Would you like to view settings from a specific group?', false))
		{
			$selectedGroup = select(
				'Select a group to view its settings:',
				$groups->prepend('default')->unique()->toArray()
			);

			$this->newLine();

			// Call the list command for the selected group
			$groupOption      = $selectedGroup === 'default' ? null : $selectedGroup;
			$settingsInstance = $groupOption ? settings()->group($groupOption) : settings();
			$settings         = $settingsInstance->all();

			if ($settings->isEmpty())
			{
				$groupInfo = $groupOption ? " in group '{$groupOption}'" : '';
				$this->info("No settings found{$groupInfo}.");
				return self::SUCCESS;
			}

			$groupInfo = $groupOption ? " (Group: {$groupOption})" : ' (Group: default)';
			$this->info("Settings{$groupInfo}:");
			$this->table(
				['Key', 'Value', 'Type'],
				$settings->map(fn($value, $key) => [
					'Key'   => $key,
					'Value' => is_scalar($value) ? (string) $value : json_encode($value),
					'Type'  => gettype($value),
				])->values()->toArray()
			);
		}

		return self::SUCCESS;
	}
}

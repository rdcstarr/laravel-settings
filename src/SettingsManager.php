<?php

namespace Rdcstarr\Settings;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use Rdcstarr\Settings\Models\Setting;
use Throwable;

class SettingsManager
{
	/**
	 * Group name for settings (future use).
	 */
	protected string $group = 'default';

	/**
	 * Retrieve all settings from the cache or database.
	 *
	 * @return Collection A collection of all settings as key-value pairs.
	 */
	public function all(): Collection
	{
		return Cache::tags(['settings', "settings.group.{$this->group}"])
			->rememberForever("data", fn() => Setting::whereGroup($this->group)->pluck('value', 'key'));
	}

	/**
	 * Set the group for settings (future use).
	 *
	 * @param  string|null  $group  The group name to set.
	 * @return $this A new instance of Settings with the specified group.
	 */
	public function group(?string $group): self
	{
		$clone        = clone $this;
		$clone->group = blank($group) ? 'default' : trim($group);

		return $clone;
	}

	/**
	 * Get the value of a specific setting by its key.
	 *
	 * @param  string  $key  The setting key to retrieve.
	 * @param  mixed  $default  The default value to return if the key doesn't exist.
	 * @return mixed The setting value or the default value if not found.
	 * @throws InvalidArgumentException If the key doesn't exist and no default is provided.
	 */
	public function get(string $key, mixed $default = ''): mixed
	{
		if (blank($default) && !$this->has($key))
		{
			throw new InvalidArgumentException("Settings key '{$key}' doesn't exist for group '{$this->group}'.");
		}

		return $this->all()->get($key, $default);
	}

	/**
	 * Get multiple setting values by their keys.
	 *
	 * @param  array  $keys  An array of setting keys to retrieve.
	 * @return array An associative array containing the requested key-value pairs.
	 */
	public function getMany(array $keys): array
	{
		$all = $this->all();

		$missingKeys = collect($keys)->reject(fn($key) => $all->has($key));

		$missingKeys->whenNotEmpty(function ($missing)
		{
			$firstMissing = $missing->first();
			throw new InvalidArgumentException("Settings key '{$firstMissing}' doesn't exist for group '{$this->group}'.");
		});

		return collect($keys)->mapWithKeys(fn($key) => [$key => $all->get($key)])->all();
	}

	/**
	 * Set a single setting value or multiple settings at once.
	 *
	 * @param  string|array  $key  The setting key (string) or an array of key-value pairs.
	 * @param  mixed  $value  The value to set (ignored when $key is an array).
	 * @return bool
	 */
	public function set(string|array $key, mixed $value = null): bool
	{
		if (is_array($key))
		{
			return $this->setMany($key);
		}

		try
		{
			Setting::updateOrCreate(
				['group' => $this->group, 'key' => $key],
				['value' => $value]
			);

			$this->flushCache();

			return true;

		}
		catch (Exception $e)
		{
			return false;
		}
	}

	/**
	 * Set multiple settings in a single batch operation.
	 *
	 * @param  array  $settings  An associative array of key-value pairs to store.
	 * @return bool
	 * @throws InvalidArgumentException If the values array is empty.
	 */
	public function setMany(array $settings): bool
	{
		if (empty($settings))
		{
			throw new InvalidArgumentException('Values array cannot be empty.');
		}

		try
		{

			$data = collect($settings)->map(fn($value, $key) => [
				'group'      => $this->group,
				'key'        => $key,
				'value'      => $value,
				'created_at' => now(),
				'updated_at' => now(),
			])->values()->toArray();

			Setting::upsert($data, ['group', 'key'], ['value', 'updated_at']);
			$this->flushCache();

			return true;
		}
		catch (Exception $e)
		{
			return false;
		}
	}

	/**
	 * Check if a setting key exists in the storage.
	 *
	 * @param  string  $key  The setting key to check for existence.
	 * @return bool True if the key exists, false otherwise.
	 */
	public function has(string $key): bool
	{
		return $this->all()->has($key);
	}

	/**
	 * Get all distinct groups from the settings table.
	 *
	 * @return Collection A collection of all group names.
	 */
	public function getAllGroups(): Collection
	{
		return Setting::distinct('group')->pluck('group');
	}

	/**
	 * Remove a setting by its key from the storage.
	 *
	 * @param  string  $key  The setting key to remove.
	 * @return void
	 */
	public function forget(string $key): void
	{
		$deleted = Setting::where([
			'group' => $this->group,
			'key'   => $key,
		])->delete();

		if ($deleted > 0)
		{
			$this->flushCache();
		}
	}

	/**
	 * Clear all settings cache for all groups.
	 *
	 * @return bool
	 */
	public function flushAllCache(): bool
	{
		try
		{
			Cache::tags(['settings'])->flush();
			return true;
		}
		catch (Exception $e)
		{
			return false;
		}
	}

	/**
	 * Clear the settings cache to force fresh data retrieval.
	 *
	 * @return void
	 */
	public function flushCache(): bool
	{
		try
		{
			Cache::tags(["settings.group.{$this->group}"])->flush();
			return true;
		}
		catch (Exception $e)
		{
			return false;
		}
	}
}

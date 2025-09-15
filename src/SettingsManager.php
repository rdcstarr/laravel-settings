<?php

namespace Rdcstarr\Settings;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Rdcstarr\Settings\Models\Setting;
use Throwable;

class SettingsManager
{
	/**
	 * The cache key used for storing settings.
	 *
	 * @var string
	 */
	protected string $cacheKey = 'app_settings';

	/**
	 * Group name for settings (future use).
	 *
	 * @var string
	 */
	protected string $group = 'default';

	/**
	 * Retrieve all settings from the cache or database.
	 *
	 * @return Collection A collection of all settings as key-value pairs.
	 */
	public function all(): Collection
	{
		return Cache::rememberForever($this->cacheKey(), fn() => Setting::whereGroup($this->group)->pluck('value', 'key'));
	}

	/**
	 * Set the group for settings (future use).
	 *
	 * @param string|null $group The group name to set.
	 * @return $this A new instance of Settings with the specified group.
	 */
	public function group(?string $group): self
	{
		$clone        = clone $this;
		$clone->group = ($group && trim($group) !== '') ? trim($group) : 'default';
		return $clone;
	}

	/**
	 * Get the value of a specific setting by its key.
	 *
	 * @param string $key The setting key to retrieve.
	 * @param mixed $default The default value to return if the key doesn't exist.
	 * @return mixed The setting value or the default value if not found.
	 */
	public function get(string $key, mixed $default = null): mixed
	{
		return $this->all()->get($key, $default);
	}

	/**
	 * Get multiple setting values by their keys.
	 *
	 * @param array $keys An array of setting keys to retrieve.
	 * @return array An associative array containing the requested key-value pairs.
	 */
	public function getMany(array $keys): array
	{
		$all = $this->all();

		return collect($keys)->mapWithKeys(fn($key) => [$key => $all->get($key)])->all();
	}

	/**
	 * Set a single setting value or multiple settings at once.
	 *
	 * @param string|array $key The setting key (string) or an array of key-value pairs.
	 * @param mixed $value The value to set (ignored when $key is an array).
	 * @return bool True if the operation was successful, false otherwise.
	 */
	public function set(string|array $key, mixed $value = null): bool
	{
		try
		{
			if (is_array($key))
			{
				return $this->setMany($key);
			}

			Setting::updateOrCreate(
				['group' => $this->group, 'key' => $key],
				['value' => $value]
			);

			$this->flushCache();

			return true;
		}
		catch (Throwable $e)
		{
			report($e);
			return false;
		}
	}

	/**
	 * Set multiple settings in a single batch operation.
	 *
	 * @param array $settings An associative array of key-value pairs to store.
	 * @return bool True if the operation was successful, false otherwise.
	 */
	public function setMany(array $settings): bool
	{
		if (empty($settings))
		{
			return true;
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
		catch (Throwable $e)
		{
			report($e);
			return false;
		}
	}

	/**
	 * Check if a setting key exists in the storage.
	 *
	 * @param string $key The setting key to check for existence.
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
	 * @param string $key The setting key to remove.
	 * @return bool True if the key was successfully deleted, false if not found or on error.
	 */
	public function forget(string $key): bool
	{
		try
		{
			$deleted = Setting::where([
				'group' => $this->group,
				'key'   => $key,
			])->delete();

			if ($deleted > 0)
			{
				$this->flushCache();
				return true;
			}

			return false;
		}
		catch (Throwable $e)
		{
			report($e);
			return false;
		}
	}

	/**
	 * Clear all settings cache for all groups.
	 *
	 * @return bool True if all caches were successfully cleared, false otherwise.
	 */
	public function flushAllCache(): bool
	{
		try
		{
			Setting::distinct('group')->pluck('group')->each(function ($group)
			{
				Cache::forget("{$this->cacheKey}:{$group}");
			});

			return true;
		}
		catch (Throwable $e)
		{
			return false;
		}
	}

	/**
	 * Clear the settings cache to force fresh data retrieval.
	 *
	 * @return bool True if the cache was successfully cleared, false otherwise.
	 */
	public function flushCache(): bool
	{
		try
		{
			Cache::forget($this->cacheKey());
			return true;
		}
		catch (Throwable $e)
		{
			return false;
		}
	}

	/**
	 * Cheia de cache pentru grupul curent.
	 */
	protected function cacheKey(): string
	{
		return "{$this->cacheKey}:{$this->group}";
	}
}

<?php

namespace Rdcstarr\Settings;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Rdcstarr\Settings\Models\Setting;

class Settings
{
	protected string $group = 'default';
	protected string $settingsCacheKey = 'app_settings';

	/**
	 * Cache for the current request (RAM)
	 */
	private Collection $cache;

	/**
	 * Retrieves all settings for the current group from cache or database.
	 *
	 * @return Collection The collection of settings as key-value pairs.
	 */
	public function all(): Collection
	{
		return $this->cache ??= Cache::rememberForever($this->getSettingsCacheKey(), fn() => Setting::group($this->group)->pluck('value', 'key'));
	}

	/**
	 * Gets the value of a specific setting key for the current group.
	 *
	 * @param string $key The setting key to retrieve.
	 * @param mixed $default The default value to return if the key does not exist.
	 * @return mixed The value of the setting or the default value.
	 */
	public function get(string $key, mixed $default = null): mixed
	{
		return $this->all()->get($key, $default);
	}

	/**
	 * Sets the value for a specific setting key or multiple keys for the current group.
	 *
	 * @param string|array $key The setting key or an array of key-value pairs.
	 * @param mixed $val The value to set for the key (ignored if $key is array).
	 * @return bool True on success, false on failure.
	 */
	public function set(string|array $key, mixed $val = null): bool
	{
		try
		{
			if (is_array($key))
			{
				$this->setBatch($key);
				return true;
			}

			Setting::updateOrCreate(
				[
					'key'   => $key,
					'group' => $this->group,
				],
				[
					'value' => $val,
				]
			);

			$cacheKey = $this->getSettingsCacheKey();
			$cached   = Cache::get($cacheKey, collect());
			$cached->put($key, $val);
			Cache::forever($cacheKey, $cached);

			return true;
		}
		catch (Exception $e)
		{
			return false;
		}
	}

	/**
	 * Checks if a specific setting key exists in the current group.
	 *
	 * @param string $key The setting key to check.
	 * @return bool True if the key exists, false otherwise.
	 */
	public function has(string $key): bool
	{
		return $this->all()->has($key);
	}

	/**
	 * Removes a specific setting key from the current group.
	 *
	 * @param string $key The setting key to remove.
	 * @return bool True on success, false on failure.
	 */
	public function forget(string $key): bool
	{
		try
		{
			Setting::where('key', $key)
				->group($this->group)
				->delete();

			$this->flushCache();

			return true;
		}
		catch (Exception)
		{
			return false;
		}
	}

	/**
	 * Flushes the settings cache for the current group.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function flushCache(): bool
	{
		try
		{
			Cache::forget($this->getSettingsCacheKey());
			unset($this->cache);
			$this->all();

			return true;
		}
		catch (Exception)
		{
			return false;
		}
	}

	/**
	 * Sets the group context for subsequent settings operations.
	 *
	 * @param string $groupName The name of the group to set.
	 * @return static The current instance for method chaining.
	 */
	public function group(string $groupName): static
	{
		if ($this->group !== $groupName)
		{
			unset($this->cache);
			$this->group = $groupName;
		}

		return $this;
	}

	/**
	 * Sets multiple settings in batch for the current group.
	 *
	 * @param array $settings An associative array of key-value pairs to set.
	 * @return bool True on success, false on failure.
	 */
	protected function setBatch(array $settings): bool
	{
		try
		{
			$data = collect($settings)->map(fn($value, $key) => [
				'key'        => $key,
				'group'      => $this->group,
				'value'      => $value,
				'created_at' => now(),
				'updated_at' => now(),
			])->values()->toArray();

			Setting::upsert(
				$data,
				['key', 'group'],
				['value', 'updated_at']
			);

			$this->flushCache();

			return true;
		}
		catch (Exception)
		{
			return false;
		}
	}

	/**
	 * Generates the cache key for the current group.
	 *
	 * @return string The cache key string.
	 */
	protected function getSettingsCacheKey(): string
	{
		return "{$this->settingsCacheKey}.{$this->group}";
	}
}

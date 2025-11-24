<?php

namespace Rdcstarr\Settings;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Rdcstarr\Settings\Models\Setting;
use Throwable;

class Settings
{
	/**
	 * The cache key used to store settings.
	 */
	protected string $cacheKey = 'settings.data';

	/**
	 * Retrieve all settings from the cache or database.
	 *
	 * @return Collection A collection of all settings as key-value pairs.
	 */
	public function all(): Collection
	{
		try
		{
			return Cache::rememberForever(
				$this->cacheKey,
				fn() => Setting::pluck('value', 'key')
			);
		}
		catch (Throwable $e)
		{
			report($e);
			return collect();
		}
	}

	/**
	 * Retrieve a setting value by key.
	 *
	 * @param string $key The setting key to retrieve.
	 * @param mixed $default Default value to return if key doesn't exist (default: false).
	 * @return mixed The setting value or default.
	 */
	public function get(string $key, mixed $default = false): mixed
	{
		if (empty($key))
		{
			return false;
		}

		$settings = $this->all();

		return $settings->get($key, $default);
	}

	/**
	 * Update or create a setting value.
	 *
	 * @param string $key The setting key.
	 * @param mixed $value The value to set.
	 * @return bool True if the value was updated, false otherwise.
	 */
	public function set(string $key, mixed $value = null): bool
	{
		if (empty($key))
		{
			return false;
		}

		try
		{
			$oldValue = $this->get($key, null);

			if ($value === $oldValue)
			{
				return false;
			}

			Setting::updateOrCreate(
				['key' => $key],
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
	 * @param array $settings An associative array of key-value pairs.
	 * @return bool True if all settings were updated, false otherwise.
	 */
	public function setMany(array $settings): bool
	{
		if (empty($settings))
		{
			return false;
		}

		try
		{
			$data = collect($settings)
				->filter(fn($value, $key) => !empty($key))
				->map(fn($value, $key) => [
					'key'        => $key,
					'value'      => $value,
					'created_at' => now(),
					'updated_at' => now(),
				])
				->values()
				->toArray();

			if (empty($data))
			{
				return false;
			}

			Setting::upsert($data, ['key'], ['value', 'updated_at']);

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
	 * Check if a setting exists.
	 *
	 * @param string $key The setting key to check.
	 * @return bool True if the key exists, false otherwise.
	 */
	public function has(string $key): bool
	{
		if (empty($key))
		{
			return false;
		}

		return $this->all()->has($key);
	}

	/**
	 * Remove a setting from storage.
	 *
	 * @param string $key The setting key to remove.
	 * @return bool True if deleted, false otherwise.
	 */
	public function delete(string $key): bool
	{
		if (empty($key))
		{
			return false;
		}

		try
		{
			$deleted = Setting::where('key', $key)->delete();

			if ($deleted === 0)
			{
				return false;
			}

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
	 * Clear the settings cache.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function flushCache(): bool
	{
		try
		{
			return Cache::forget($this->cacheKey);
		}
		catch (Throwable $e)
		{
			report($e);
			return false;
		}
	}
}

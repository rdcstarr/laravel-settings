<?php

namespace Rdcstarr\Settings;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Rdcstarr\Settings\Models\Setting;

class Settings
{
	/**
	 * Cache for the current request (RAM)
	 *
	 * @var ?Collection The cached settings for the current request.
	 */
	protected ?Collection $cache = null;

	/**
	 * Cache key for the settings.
	 *
	 * @var string
	 */
	protected string $cacheKey = 'app_settings';

	/**
	 * Retrieves all settings from cache or database.
	 *
	 * @return Collection The collection of settings as key-value pairs.
	 */
	public function all(): Collection
	{
		return $this->cache ??= Cache::rememberForever($this->cacheKey, fn() => Setting::all()->pluck('value', 'key'));
	}

	/**
	 * Gets the value of a specific setting key.
	 *
	 * @param string $key The setting key to retrieve.
	 * @param ?string $default The default value to return if the key does not exist.
	 * @return mixed The value of the setting or the default value.
	 */
	public function get(string $key, ?string $default = null): mixed
	{
		return $this->all()->get($key, $default);
	}

	/**
	 * Sets the value for a specific setting key or multiple keys.
	 *
	 * @param string|array $key The setting key or an array of key-value pairs.
	 * @param mixed $val The value to set for the key (ignored if $key is array).
	 * @return bool True on success, false on failure.
	 * @throws Exception If database operation fails.
	 */
	public function set(string|array $key, mixed $val = null): bool
	{
		if (is_array($key))
		{
			return $this->setBatch($key);
		}

		$setting = Setting::updateOrCreate(
			['key' => $key],
			['value' => $val]
		);

		$setting && $this->updateCacheDirectly(keysToAdd: [$key => $val]);

		return (bool) $setting;
	}

	/**
	 * Checks if a specific setting key exists.
	 *
	 * @param string $key The setting key to check.
	 * @return bool True if the key exists, false otherwise.
	 */
	public function has(string $key): bool
	{
		return $this->all()->has($key);
	}

	/**
	 * Removes a specific setting key.
	 *
	 * @param string $key The setting key to remove.
	 * @return bool True if the key was deleted, false if no key was found.
	 * @throws Exception If database operation fails.
	 */
	public function forget(string $key): bool
	{
		$deleted = Setting::whereKey($key)->delete();
		$deleted && $this->updateCacheDirectly(keysToRemove: [$key]);

		return (bool) $deleted;
	}

	/**
	 * Flushes the settings cache.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function flushCache(): bool
	{
		$cache = Cache::forget($this->cacheKey);
		$cache && $this->cache = null;

		return (bool) $cache;
	}

	/**
	 * Sets multiple settings in batch.
	 *
	 * @param array $settings An associative array of key-value pairs to set.
	 * @return bool True on success, false on failure.
	 * @throws Exception If database operation fails.
	 */
	protected function setBatch(array $settings): bool
	{
		$data = collect($settings)->map(fn($value, $key) => [
			'key'        => $key,
			'value'      => $value,
			'created_at' => now(),
			'updated_at' => now(),
		])->values()->toArray();

		$setting = Setting::upsert($data, ['key'], ['value', 'updated_at']);
		$setting && $this->updateCacheDirectly(keysToAdd: $settings);

		return (bool) $setting;
	}

	/**
	 * Updates the cache by adding or removing specific keys without flushing the entire cache.
	 *
	 * @param array $keysToAdd Associative array of keys to add/update
	 * @param array $keysToRemove Array of keys to remove
	 * @return void
	 */
	protected function updateCacheDirectly(array $keysToAdd = [], array $keysToRemove = []): void
	{
		$cached = Cache::get($this->cacheKey);

		if (!$cached instanceof Collection)
		{
			$this->flushCache();
			return;
		}

		$updater = fn(Collection $cache) => $cache
			->when(!empty($keysToRemove), fn($c) => $c->except($keysToRemove))
			->when(!empty($keysToAdd), fn($c) => $c->merge($keysToAdd));

		Cache::forever($this->cacheKey, $updater($cached));

		// Update RAM cache if it exists
		if (isset($this->cache))
		{
			$this->cache = $updater($this->cache);
		}
	}
}

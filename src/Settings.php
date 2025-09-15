<?php

namespace Rdcstarr\Settings;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use Rdcstarr\Settings\Models\Setting;
use Throwable;

class Settings
{

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
		return Cache::rememberForever($this->cacheKey, fn() => $this->getDataFromModel());
	}

	/**
	 * Gets the value of a specific setting key.
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
	 * Sets the value for a specific setting key or multiple keys.
	 *
	 * @param string|array $key The setting key or an array of key-value pairs.
	 * @param mixed $value The value to set for the key (ignored if $key is array).
	 * @return bool True on success, false on failure.
	 */
	public function set(string|array $key, mixed $value = null): bool
	{
		if (is_array($key))
		{
			return $this->setBatch($key);
		}

		try
		{
			Setting::updateOrCreate(
				['key' => $key],
				['value' => $value]
			);

			$this->handleCache("add", [$key => $value]);

			return true;
		}
		catch (Throwable $e)
		{
			$this->flushCache();
			return false;
		}
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
		try
		{
			$deleted = Setting::where('key', $key)->delete();

			if ($deleted > 0)
			{
				$this->handleCache("remove", $key);
			}

			return $deleted > 0;
		}
		catch (Throwable $e)
		{
			$this->flushCache();
			return false;
		}
	}

	/**
	 * Flushes the settings cache.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function flushCache(): bool
	{
		try
		{
			Cache::forget($this->cacheKey);
			return true;
		}
		catch (Throwable $e)
		{
			return false;
		}
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
		try
		{
			$data = collect($settings)->map(fn($value, $key) => [
				'key'        => $key,
				'value'      => $value,
				'created_at' => now(),
				'updated_at' => now(),
			])->values()->toArray();

			Setting::upsert($data, ['key'], ['value', 'updated_at']);
			$this->handleCache("add", $settings);

			return true;
		}
		catch (Throwable $e)
		{
			$this->flushCache();
			return false;
		}
	}

	protected function handleCache(string $action, array|string|null $data = null)
	{
		$cache = Cache::get($this->cacheKey);

		if (!$cache instanceof Collection)
		{
			$cache = $this->getDataFromModel();
		}

		match ($action)
		{
			'add' => $this->addToCache($cache, $data),
			'remove' => $this->removeFromCache($cache, $data),
			default => throw new InvalidArgumentException("Unknown action: {$action}")
		};

		Cache::forever($this->cacheKey, $cache);
	}

	private function addToCache(Collection $cache, mixed $data): void
	{
		if (!is_array($data))
		{
			throw new InvalidArgumentException('Data must be an array when action is add.');
		}

		foreach ($data as $key => $value)
		{
			$cache->put($key, $value);
		}
	}

	private function removeFromCache(Collection $cache, mixed $data): void
	{
		if (!is_string($data))
		{
			throw new InvalidArgumentException('Data must be a string when action is remove.');
		}

		$cache->forget($data);
	}

	private function getDataFromModel(): Collection
	{
		return Setting::all()->pluck('value', 'key');
	}
}

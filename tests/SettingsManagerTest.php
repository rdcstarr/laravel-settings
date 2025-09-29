<?php

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use RuntimeException;
use Rdcstarr\Settings\Exceptions\SettingsOperationException;
use Rdcstarr\Settings\Models\Setting;
use Rdcstarr\Settings\SettingsManager;

class TestSettingsManager extends SettingsManager
{
	public ?bool $forcedFlushResult = true;
	public ?Collection $forcedAllResult = null;
	public ?bool $forcedHasResult = null;

	public function flushCache(): bool
	{
		if ($this->forcedFlushResult !== null)
		{
			return $this->forcedFlushResult;
		}

		return parent::flushCache();
	}

	public function all(): Collection
	{
		if ($this->forcedAllResult !== null)
		{
			return $this->forcedAllResult;
		}

		return parent::all();
	}

	public function has(string $key): bool
	{
		if ($this->forcedHasResult !== null)
		{
			return $this->forcedHasResult;
		}

		return parent::has($key);
	}
}

describe('SettingsManager error reporting', function ()
{
	beforeEach(function ()
	{
		Setting::query()->delete();
	});

	it('throws when retrieving a missing key', function ()
	{
		$manager                  = new TestSettingsManager();
		$manager->forcedHasResult = false;

		expect(fn() => $manager->get('missing'))->toThrow(InvalidArgumentException::class);
	});

	it('returns stored value when key exists', function ()
	{
		$manager                  = new TestSettingsManager();
		$manager->forcedHasResult = true;
		$manager->forcedAllResult = collect(['app.name' => 'Laravel']);

		expect($manager->get('app.name'))->toBe('Laravel');
	});

	it('throws when cache flush fails after set', function ()
	{
		$manager                    = new TestSettingsManager();
		$manager->forcedFlushResult = false;

		expect(fn() => $manager->set('app.timezone', 'UTC'))->toThrow(SettingsOperationException::class);
	});

	it('persists value on set and returns true when flush succeeds', function ()
	{
		$manager = new TestSettingsManager();

		expect($manager->set('app.locale', 'ro'))->toBeTrue();

		expect(optional(Setting::where('group', 'default')->where('key', 'app.locale')->first())->value)->toBe('ro');
	});

	it('throws when cache flush fails after setMany', function ()
	{
		$manager                    = new TestSettingsManager();
		$manager->forcedFlushResult = false;

		expect(fn() => $manager->setMany(['app.locale' => 'ro']))->toThrow(SettingsOperationException::class);
	});

	it('persists multiple values via setMany when flush succeeds', function ()
	{
		$manager = new TestSettingsManager();

		expect($manager->setMany([
			'app.locale'   => 'ro',
			'app.timezone' => 'UTC',
		]))->toBeTrue();

		expect(Setting::whereGroup('default')->pluck('value', 'key')->all())->toMatchArray([
			'app.locale'   => 'ro',
			'app.timezone' => 'UTC',
		]);
	});

	it('returns false when forgetting a missing key', function ()
	{
		$manager = new SettingsManager();

		expect($manager->forget('missing'))->toBeFalse();
	});

	it('returns true when forgetting existing key and cache flush succeeds', function ()
	{
		Setting::create([
			'group' => 'default',
			'key'   => 'app.locale',
			'value' => 'ro',
		]);

		$manager = new TestSettingsManager();

		expect($manager->forget('app.locale'))->toBeTrue();
	});

	it('returns false when cache flush fails while forgetting existing key', function ()
	{
		Setting::create([
			'group' => 'default',
			'key'   => 'app.locale',
			'value' => 'ro',
		]);

		$manager                    = new TestSettingsManager();
		$manager->forcedFlushResult = false;

		expect($manager->forget('app.locale'))->toBeFalse();
	});

	it('returns cache result when flushing group cache', function ()
	{
		Cache::shouldReceive('tags')
			->once()
			->with(['settings.group.default'])
			->andReturnSelf();
		Cache::shouldReceive('flush')
			->once()
			->andReturn(true);

		expect((new SettingsManager())->flushCache())->toBeTrue();
	});

	it('returns false when flushing group cache throws an exception', function ()
	{
		Cache::shouldReceive('tags')
			->once()
			->with(['settings.group.default'])
			->andThrow(new RuntimeException('cache failure'));

		expect((new SettingsManager())->flushCache())->toBeFalse();
	});

	it('returns cache result when flushing all settings cache', function ()
	{
		Cache::shouldReceive('tags')
			->once()
			->with(['settings'])
			->andReturnSelf();
		Cache::shouldReceive('flush')
			->once()
			->andReturn(true);

		expect((new SettingsManager())->flushAllCache())->toBeTrue();
	});

	it('returns false when flushing all cache throws an exception', function ()
	{
		Cache::shouldReceive('tags')
			->once()
			->with(['settings'])
			->andThrow(new RuntimeException('cache failure'));

		expect((new SettingsManager())->flushAllCache())->toBeFalse();
	});
});

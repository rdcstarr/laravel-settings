<?php

namespace Rdcstarr\Settings\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Rdcstarr\Settings\SettingsServiceProvider;

class TestCase extends Orchestra
{
	protected function getPackageProviders($app)
	{
		return [SettingsServiceProvider::class];
	}

	protected function defineEnvironment($app): void
	{
		$app['config']->set('database.default', 'testbench');
		$app['config']->set('database.connections.testbench', [
			'driver'   => 'sqlite',
			'database' => ':memory:',
			'prefix'   => '',
		]);

		$app['config']->set('cache.default', 'array');
	}

	protected function setUp(): void
	{
		parent::setUp();

		$this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
	}
}

<?php

namespace Rdcstarr\Settings\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Rdcstarr\Settings\Settings
 */
class Settings extends Facade
{
	protected static function getFacadeAccessor(): string
	{
		return \Rdcstarr\Settings\Settings::class;
	}
}

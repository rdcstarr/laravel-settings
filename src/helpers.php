<?php

if (!function_exists('settings'))
{
	/**
	 * Get or set app settings stored in db.
	 *
	 * @param string|null $key
	 * @param mixed $default
	 * @return mixed
	 */
	function settings(?string $key = null, mixed $default = '')
	{
		$setting = app('settings');

		if ($key === null)
		{
			return $setting;
		}

		return $setting->get($key, $default);
	}
}

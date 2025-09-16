<?php

if (!function_exists('settings'))
{
	/**
	 * Get or set app settings stored in db.
	 *
	 * @param  mixed  $key
	 * @param  mixed  $default
	 * @return mixed
	 */
	function settings($key = null, $default = null)
	{
		$setting = app('settings');

		if ($key === null)
		{
			return $setting;
		}

		return $setting->get($key, $default);
	}
}

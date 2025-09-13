<?php

if (!function_exists('settings'))
{
	/**
	 * Get app setting stored in db.
	 *
	 * @param $key
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

		return $setting->get($key, value($default));
	}
}

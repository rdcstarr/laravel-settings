<?php

namespace Rdcstarr\Settings\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Setting extends Model
{
	protected $fillable = [
		'group',
		'key',
		'value',
	];

	protected $casts = [
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
	];

	/**
	 * Cast value to appropriate type when retrieving.
	 */
	protected function value(): Attribute
	{
		return Attribute::make(
			get: fn($value) => $this->castValue($value),
			set: fn($value) => $this->encodeValue($value)
		);
	}

	/**
	 * Auto-cast string value to appropriate type.
	 *
	 * @param  string|null  $value
	 * @return mixed
	 */
	protected function castValue($value)
	{
		if ($value === null)
		{
			return null;
		}

		$value = Str::trim($value);

		// Check for boolean values
		if (Str::lower($value) === 'true')
		{
			return true;
		}

		if (Str::lower($value) === 'false')
		{
			return false;
		}

		// Check for null
		if (Str::lower($value) === 'null')
		{
			return null;
		}

		// Check for integer
		if (Str::startsWith($value, '-') && ctype_digit(Str::substr($value, 1)))
		{
			return (int) $value;
		}
		if (ctype_digit($value))
		{
			return (int) $value;
		}

		// Check for float
		if (is_numeric($value) && Str::contains($value, '.'))
		{
			return (float) $value;
		}

		// Check for JSON (array or object)
		if (Str::isJson(value: $value))
		{
			return json_decode($value, true);
		}

		// Return as string
		return $value;
	}

	/**
	 * Encode value for storage.
	 *
	 * @param  mixed  $value
	 */
	protected function encodeValue($value): string
	{
		return match (true)
		{
			$value === null => 'null',
			is_bool($value) => $value ? 'true' : 'false',
			Arr::arrayable($value) => json_encode($value),
			default => (string) $value
		};
	}
}

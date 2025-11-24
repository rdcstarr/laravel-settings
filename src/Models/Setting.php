<?php

namespace Rdcstarr\Settings\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Setting extends Model
{
	protected $fillable = [
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
		if ($value === null || $value === '')
		{
			return null;
		}

		// Quick check: if not a string, return as-is
		if (!is_string($value))
		{
			return $value;
		}

		$trimmed = trim($value);

		// Check for literal 'null' string
		if (strcasecmp($trimmed, 'null') === 0)
		{
			return null;
		}

		// Check for boolean values
		if (strcasecmp($trimmed, 'true') === 0)
		{
			return true;
		}

		if (strcasecmp($trimmed, 'false') === 0)
		{
			return false;
		}

		// Check for JSON (array or object) - do this before numeric checks
		if (strlen($trimmed) > 0 && ($trimmed[0] === '{' || $trimmed[0] === '[') && Str::isJson($trimmed))
		{
			return json_decode($trimmed, true);
		}

		// Check for numeric values (int or float)
		if (is_numeric($trimmed))
		{
			// Contains decimal point or scientific notation
			if (str_contains($trimmed, '.') || stripos($trimmed, 'e') !== false)
			{
				return (float) $trimmed;
			}

			// Integer - but preserve leading zeros by checking if it's a "clean" integer
			$intValue = (int) $trimmed;

			// If string representation matches original, it's a true integer
			if ((string) $intValue === $trimmed)
			{
				return $intValue;
			}
		}

		// Return as string (original, not trimmed, to preserve formatting)
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

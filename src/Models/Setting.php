<?php

namespace Rdcstarr\Settings\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
	protected $fillable = [
		'key',
		'group',
		'value',
	];

	protected $casts = [
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
	];
	protected $attributes = [
		'group' => 'default',
	];

	public function scopeGroup($query, $groupName)
	{
		return $query->whereGroup($groupName);
	}
}

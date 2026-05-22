<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiPrompt extends Model
{
    protected $primaryKey = 'key';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['key', 'group', 'label', 'value', 'description', 'sort_order'];

    public static function get(string $key, string $default = ''): string
    {
        return static::find($key)?->value ?? $default;
    }

    public function scopeGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('key');
    }

    public static function groups(): array
    {
        return static::whereNotNull('group')
            ->selectRaw('MIN(sort_order) as sort, `group`')
            ->groupBy('group')
            ->orderBy('sort')
            ->orderBy('group')
            ->pluck('group')
            ->toArray();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthSetting extends Model
{
    protected $table = 'settings_site_auth';

    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null): ?string
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function allSettings(): array
    {
        return static::pluck('value', 'key')->toArray();
    }
}

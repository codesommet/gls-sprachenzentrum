<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemConfig extends Model
{
    protected $fillable = ['key', 'value', 'type', 'description'];

    /**
     * Get a config value with type casting.
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember("sysconfig.{$key}", 3600, function () use ($key, $default) {
            $config = self::where('key', $key)->first();
            if (!$config) return $default;

            return match ($config->type) {
                'integer' => (int) $config->value,
                'float' => (float) $config->value,
                'boolean' => filter_var($config->value, FILTER_VALIDATE_BOOLEAN),
                'array' => array_map('trim', explode(',', $config->value)),
                default => $config->value,
            };
        });
    }

    /**
     * Set a config value.
     */
    public static function set(string $key, $value): void
    {
        $config = self::firstOrNew(['key' => $key]);
        if (is_array($value)) {
            $value = implode(',', $value);
        }
        $config->value = (string) $value;
        $config->save();
        Cache::forget("sysconfig.{$key}");
    }
}

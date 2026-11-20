<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    /**
     * Cache key prefix for system settings.
     */
    public const CACHE_PREFIX = 'system_setting:';

    /**
     * Get a setting value with caching and fallback.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        return Cache::rememberForever(self::CACHE_PREFIX.$key, function () use ($key, $default) {
            $setting = static::query()->where('key', $key)->first();

            return $setting?->value ?? $default;
        });
    }

    /**
     * Set a setting value, clear cache, and record audit log.
     */
    public static function set(string $key, ?string $value, ?string $description = null, ?User $byUser = null): static
    {
        $setting = static::query()->firstOrNew(['key' => $key]);
        $oldValue = $setting->exists ? $setting->value : null;

        $setting->value = $value;
        if ($description !== null) {
            $setting->description = $description;
        }
        $setting->save();

        Cache::forget(self::CACHE_PREFIX.$key);

        // Record audit log
        SystemSettingLog::query()->create([
            'user_id' => $byUser?->id,
            'setting_key' => $key,
            'old_value' => $oldValue,
            'new_value' => $value,
            'created_at' => now(),
        ]);

        return $setting;
    }

    /**
     * Helper to retrieve dynamic institution branding display name.
     */
    public static function getInstitutionName(): string
    {
        $name = self::get('institution_display_name', 'PPDBM MIN 1 KOTA MADIUN');

        return ! empty($name) ? $name : 'PPDBM MIN 1 KOTA MADIUN';
    }
}

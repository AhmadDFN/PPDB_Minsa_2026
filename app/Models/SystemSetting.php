<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
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
     * Includes graceful recovery when migration is temporarily pending.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        $cacheKey = self::CACHE_PREFIX.$key;

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $setting = static::query()->where('key', $key)->first();
            $value = $setting?->value ?? $default;

            // Only cache in application cache when table exists and query succeeds
            Cache::put($cacheKey, $value, now()->addHours(24));

            return $value;
        } catch (QueryException $e) {
            // Graceful degradation ONLY for missing table during deployment/migration window:
            // SQLSTATE 42S02 (Base table or view not found) / MySQL error 1146 / SQLite "no such table".
            // Return fallback default directly WITHOUT caching it permanently,
            // so the database setting is immediately used once the migration runs.
            if ($e->getCode() === '42S02' || str_contains($e->getMessage(), '1146') || str_contains($e->getMessage(), 'no such table')) {
                return $default;
            }

            // Do NOT suppress other database failures (connection loss, auth errors, deadlocks, etc.)
            throw $e;
        }
    }

    /**
     * Clear cached setting value.
     */
    public static function clearCache(?string $key = null): void
    {
        if ($key !== null) {
            Cache::forget(self::CACHE_PREFIX.$key);
        } else {
            Cache::forget(self::CACHE_PREFIX.'institution_display_name');
        }
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

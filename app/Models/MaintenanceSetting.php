<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MaintenanceSetting extends Model
{
    protected $fillable = ['key', 'is_active', 'message'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Check if maintenance mode is active for a specific role
     */
    public static function isMaintenanceActive(string $role): bool
    {
        $key = $role . '_maintenance';
        
        return Cache::remember("maintenance_{$key}", 60, function () use ($key) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->is_active : false;
        });
    }

    /**
     * Get maintenance message for a specific role
     */
    public static function getMaintenanceMessage(string $role): ?string
    {
        $key = $role . '_maintenance';
        
        return Cache::remember("maintenance_message_{$key}", 60, function () use ($key) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->message : null;
        });
    }

    /**
     * Clear maintenance cache
     */
    public static function clearCache(): void
    {
        Cache::forget('maintenance_jobseeker_maintenance');
        Cache::forget('maintenance_employer_maintenance');
        Cache::forget('maintenance_message_jobseeker_maintenance');
        Cache::forget('maintenance_message_employer_maintenance');
    }
}

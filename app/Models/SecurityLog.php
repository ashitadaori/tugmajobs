<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityLog extends Model
{
    protected $fillable = [
        'user_id',
        'event_type',
        'ip_address',
        'user_agent',
        'status',
        'details',
        'location',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Helper method to log security events
    public static function logEvent(string $eventType, ?int $userId = null, string $status = 'success', ?string $details = null): void
    {
        self::create([
            'user_id' => $userId,
            'event_type' => $eventType,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'status' => $status,
            'details' => $details,
            'location' => self::getLocationFromIp(request()->ip()),
        ]);
    }

    private static function getLocationFromIp(string $ip): ?string
    {
        // Simple location detection - you can enhance this with a GeoIP service
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return 'Local';
        }
        return 'Unknown';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Job extends Model
{
    protected $fillable = [
        'title',
        'description',
        'requirements',
        'benefits',
        'location',
        'location_name',
        'location_address',
        'address',
        'latitude',
        'longitude',
        'barangay',
        'city',
        'province',
        'salary_range',
        'experience',
        'vacancy',
        'deadline',
        'status',
        'rejection_reason',
        'approved_at',
        'rejected_at',
        'is_featured',
        'is_remote',
        'experience_level',
        'education_level'
    ];

    protected $casts = [
        'meta_data' => 'array',
        'deadline' => 'datetime',
        'is_featured' => 'boolean',
        'is_remote' => 'boolean',
        'salary_min' => 'float',
        'salary_max' => 'float',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CLOSED = 'closed';

    public function employer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employer_id');
    }

    public function jobType(): BelongsTo
    {
        return $this->belongsTo(JobType::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(JobView::class);
    }

    /**
     * Get the users who saved this job.
     */
    public function savedBy()
    {
        return $this->belongsToMany(User::class, 'saved_jobs')
            ->withTimestamps();
    }

    /**
     * Check if a user has saved this job.
     */
    public function savedByUser($userId): bool
    {
        return $this->savedBy()->where('user_id', $userId)->exists();
    }

    /**
     * Check if a user has already applied for this job.
     */
    public function hasUserApplied($userId): bool
    {
        return $this->applications()->where('user_id', $userId)->exists();
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_APPROVED)
                    ->where(function($q) {
                        $q->whereNull('deadline')
                          ->orWhere('deadline', '>', now());
                    });
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function getRejectionReason(): ?string
    {
        return $this->meta_data['rejection_reason'] ?? null;
    }

    /**
     * Get the full address of the job location
     */
    public function getFullAddress(): string
    {
        // Use existing location fields first, then fall back to new fields
        if (!empty($this->location_address)) {
            return $this->location_address;
        }
        
        if (!empty($this->address)) {
            return $this->address;
        }
        
        // Build address from components
        $parts = array_filter([
            $this->location_name ?: $this->location,
            $this->barangay,
            $this->city ?: 'Digos City',
            $this->province ?: 'Davao del Sur'
        ]);
        
        return implode(', ', $parts) ?: $this->location;
    }

    /**
     * Check if job has valid coordinates
     */
    public function hasCoordinates(): bool
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }

    /**
     * Calculate distance from given coordinates (in kilometers)
     */
    public function distanceFrom($latitude, $longitude): ?float
    {
        if (!$this->hasCoordinates()) {
            return null;
        }

        $earthRadius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($latitude - $this->latitude);
        $dLon = deg2rad($longitude - $this->longitude);

        $a = sin($dLat/2) * sin($dLat/2) + 
             cos(deg2rad($this->latitude)) * cos(deg2rad($latitude)) * 
             sin($dLon/2) * sin($dLon/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }

    /**
     * Scope to find jobs within a certain distance from coordinates
     */
    public function scopeWithinDistance($query, $latitude, $longitude, $distance = 10)
    {
        return $query->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->selectRaw("*, 
                        (6371 * acos(cos(radians(?)) 
                        * cos(radians(latitude)) 
                        * cos(radians(longitude) - radians(?)) 
                        + sin(radians(?)) 
                        * sin(radians(latitude)))) AS distance", 
                        [$latitude, $longitude, $latitude])
                    ->having('distance', '<=', $distance)
                    ->orderBy('distance');
    }

    /**
     * Scope to find jobs in Digos City
     */
    public function scopeInDigosCity($query)
    {
        return $query->where('city', 'Digos City')
                    ->orWhere('city', 'like', '%Digos%');
    }
}

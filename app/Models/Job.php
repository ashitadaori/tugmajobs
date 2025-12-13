<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsAudit;

class Job extends Model
{
    use LogsAudit, SoftDeletes;
    protected $fillable = [
        'title',
        'description',
        'posted_by_admin',
        'qualifications',
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
        'salary_min',
        'salary_max',
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
        'education_level',
        'preliminary_questions',
        'requires_screening',
        'employer_id',
        'user_id',
        'category_id',
        'job_type_id',
        'company_id',
        'company_name',
        'company_website'
    ];

    protected $casts = [
        'meta_data' => 'array',
        'preliminary_questions' => 'array',
        'requires_screening' => 'boolean',
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

    // Status constants (integer values to match database tinyint column)
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2;
    const STATUS_EXPIRED = 3;
    const STATUS_CLOSED = 4;

    /**
     * Get human-readable status name
     */
    public function getStatusNameAttribute()
    {
        $statusNames = [
            self::STATUS_PENDING => 'Pending Approval',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_EXPIRED => 'Expired',
            self::STATUS_CLOSED => 'Closed',
        ];

        return $statusNames[$this->status] ?? 'Unknown';
    }

    /**
     * Get status badge class for UI
     */
    public function getStatusBadgeClassAttribute()
    {
        $badgeClasses = [
            self::STATUS_PENDING => 'badge bg-warning',
            self::STATUS_APPROVED => 'badge bg-success',
            self::STATUS_REJECTED => 'badge bg-danger',
            self::STATUS_EXPIRED => 'badge bg-secondary',
            self::STATUS_CLOSED => 'badge bg-dark',
        ];

        return $badgeClasses[$this->status] ?? 'badge bg-light text-dark';
    }

    public function employer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employer_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function employerCompany(): BelongsTo
    {
        // Access company information through the employer's profile
        return $this->belongsTo(Employer::class, 'employer_id', 'user_id');
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

    /**
     * Get the requirements for the job.
     * Note: Use jobRequirements() to avoid conflict with 'requirements' column
     */
    public function jobRequirements(): HasMany
    {
        return $this->hasMany(JobRequirement::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Get only required requirements.
     */
    public function requiredRequirements(): HasMany
    {
        return $this->hasMany(JobRequirement::class)->where('is_required', true)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Check if job has requirements defined.
     */
    public function hasRequirements(): bool
    {
        return $this->jobRequirements()->exists();
    }

    public function views(): HasMany
    {
        return $this->hasMany(JobView::class);
    }

    public function savedByUsers(): HasMany
    {
        return $this->hasMany(SavedJob::class);
    }

    /**
     * Check if job is saved by a specific user
     */
    public function isSavedByUser($userId): bool
    {
        return $this->savedByUsers()->where('user_id', $userId)->exists();
    }

    /**
     * Get saved count for this job
     */
    public function getSavedCountAttribute(): int
    {
        return $this->savedByUsers()->count();
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
        return $this->applications()
            ->where('user_id', $userId)
            ->where('application_step', 'submitted')
            ->exists();
    }

    /**
     * Get incomplete application for a user
     */
    public function getIncompleteApplication($userId)
    {
        return $this->applications()
            ->where('user_id', $userId)
            ->where('application_step', '!=', 'submitted')
            ->first();
    }

    /**
     * Clean up old abandoned draft applications (older than 24 hours)
     */
    public function cleanupAbandonedDrafts()
    {
        return $this->applications()
            ->where('application_step', '!=', 'submitted')
            ->where('created_at', '<', now()->subHours(24))
            ->delete();
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
        return $this->rejection_reason;
    }

    public function getRejectionFeedback(): ?string
    {
        return $this->rejection_reason;
    }

    public function needsAdminReview(): bool
    {
        return $this->status === self::STATUS_PENDING;
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
            $this->city ?: 'Sta. Cruz',
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
     * Scope to find jobs in Sta. Cruz, Davao del Sur
     */
    public function scopeInStaCruz($query)
    {
        return $query->where('city', 'Sta. Cruz')
                    ->orWhere('city', 'like', '%Sta. Cruz%')
                    ->orWhere('city', 'like', '%Santa Cruz%');
    }

    /**
     * Get count of approved/accepted applications
     */
    public function getAcceptedApplicationsCountAttribute()
    {
        return $this->applications()
            ->where('status', JobApplication::STATUS_APPROVED)
            ->count();
    }

    /**
     * Check if job vacancies are filled
     */
    public function isFilled()
    {
        if (!$this->vacancy || $this->vacancy <= 0) {
            return false;
        }

        return $this->accepted_applications_count >= $this->vacancy;
    }

    /**
     * Auto-close job if vacancies are filled
     */
    public function checkAndAutoClose()
    {
        if ($this->isFilled() && $this->status === self::STATUS_APPROVED) {
            $this->update([
                'status' => self::STATUS_CLOSED
            ]);
            
            return true;
        }

        return false;
    }

    /**
     * Scope to get only open jobs (approved and not filled)
     */
    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_APPROVED)
                    ->where(function($q) {
                        $q->whereNull('vacancy')
                          ->orWhereRaw('vacancy > (SELECT COUNT(*) FROM job_applications WHERE job_applications.job_id = jobs.id AND job_applications.status = ?)', [JobApplication::STATUS_APPROVED]);
                    });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Employer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'company_slug',
        'company_description',
        'company_website',
        'company_logo',
        'gallery_images', // NEW - added in schema improvements
        'company_video', // NEW - added in schema improvements
        'company_size',
        'industry',
        'founded_year',
        'contact_person_name',
        'contact_person_designation',
        'business_email',
        'business_phone',
        'business_address',
        'city',
        'state',
        'country',
        'postal_code',
        'latitude',
        'longitude',
        'business_registration_number',
        'tax_identification_number',
        'business_documents',
        'linkedin_url',
        'facebook_url',
        'twitter_url',
        'instagram_url',
        'subscription_plan',
        'subscription_starts_at',
        'subscription_ends_at',
        'job_posts_limit',
        'job_posts_used',
        'status',
        'is_verified',
        'is_featured',
        'verified_at',
        'notification_preferences',
        'settings',
        'hiring_process', // NEW - added in schema improvements
        'company_culture', // NEW - added in schema improvements
        'benefits_offered', // NEW - added in schema improvements
        'specialties', // NEW - added in schema improvements
        'meta_title', // NEW - added in schema improvements
        'meta_description', // NEW - added in schema improvements
        'total_jobs_posted',
        'total_applications_received',
        'total_hires',
        'profile_views', // NEW - added in schema improvements
        'active_jobs', // NEW - added in schema improvements
        'average_rating',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'business_documents' => 'array',
        'gallery_images' => 'array',
        'subscription_starts_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'is_verified' => 'boolean',
        'is_featured' => 'boolean',
        'verified_at' => 'datetime',
        'notification_preferences' => 'array',
        'settings' => 'array',
        'hiring_process' => 'array',
        'company_culture' => 'array',
        'benefits_offered' => 'array',
        'specialties' => 'array',
        'average_rating' => 'decimal:2',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($employer) {
            if (empty($employer->company_slug)) {
                $employer->company_slug = Str::slug($employer->company_name);
            }
        });

        static::updating(function ($employer) {
            if ($employer->isDirty('company_name') && empty($employer->company_slug)) {
                $employer->company_slug = Str::slug($employer->company_name);
            }
        });
    }

    /**
     * Get the user that owns the employer profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all jobs posted by this employer.
     */
    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class, 'employer_id', 'user_id');
    }

    /**
     * Get active jobs only.
     */
    public function activeJobs(): HasMany
    {
        return $this->jobs()->where('status', 'published');
    }

    /**
     * Get reviews for this employer.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'employer_id', 'user_id');
    }

    /**
     * Check if employer is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if employer is verified.
     */
    public function isVerified(): bool
    {
        return $this->is_verified;
    }

    /**
     * Check if employer is featured.
     */
    public function isFeatured(): bool
    {
        return $this->is_featured;
    }

    /**
     * Check if subscription is active.
     */
    public function hasActiveSubscription(): bool
    {
        if ($this->subscription_plan === 'free') {
            return true;
        }

        return $this->subscription_ends_at && $this->subscription_ends_at->isFuture();
    }

    /**
     * Check if employer can post more jobs.
     */
    public function canPostJob(): bool
    {
        return $this->job_posts_used < $this->job_posts_limit;
    }

    /**
     * Get remaining job posts.
     */
    public function getRemainingJobPostsAttribute(): int
    {
        return max(0, $this->job_posts_limit - $this->job_posts_used);
    }

    /**
     * Get company logo URL.
     */
    public function getCompanyLogoUrlAttribute(): ?string
    {
        if ($this->company_logo) {
            return asset('storage/' . $this->company_logo);
        }

        return null;
    }

    /**
     * Get subscription status.
     */
    public function getSubscriptionStatusAttribute(): string
    {
        if ($this->subscription_plan === 'free') {
            return 'Free Plan';
        }

        if (!$this->subscription_ends_at) {
            return 'No Active Subscription';
        }

        if ($this->subscription_ends_at->isFuture()) {
            return 'Active until ' . $this->subscription_ends_at->format('M d, Y');
        }

        return 'Expired on ' . $this->subscription_ends_at->format('M d, Y');
    }

    /**
     * Get formatted company size.
     */
    public function getFormattedCompanySizeAttribute(): string
    {
        $sizes = [
            '1-10' => '1-10 employees',
            '11-50' => '11-50 employees',
            '51-200' => '51-200 employees',
            '201-500' => '201-500 employees',
            '501-1000' => '501-1000 employees',
            '1000+' => '1000+ employees',
        ];

        return $sizes[$this->company_size] ?? $this->company_size ?? 'Not specified';
    }

    /**
     * Scope for active employers.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for verified employers.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope for featured employers.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Get profile completion percentage.
     * Calculates based on filled required fields.
     */
    public function getProfileCompletionPercentage(): int
    {
        $requiredFields = [
            'company_name',
            'company_description',
            'industry',
            'company_size',
            'business_email',
            'business_phone',
            'business_address',
            'city',
            'state',
            'country',
        ];

        $optionalFields = [
            'company_logo',
            'company_culture',
            'benefits_offered',
        ];

        $totalFields = count($requiredFields) + count($optionalFields);
        $filledFields = 0;

        // Check required fields (weighted more heavily)
        foreach ($requiredFields as $field) {
            if (!empty($this->$field)) {
                $filledFields += 1.5; // Required fields count more
            }
        }

        // Check optional fields
        foreach ($optionalFields as $field) {
            if (!empty($this->$field)) {
                $filledFields += 1;
            }
        }

        // Calculate percentage
        $maxScore = (count($requiredFields) * 1.5) + count($optionalFields);
        $percentage = ($filledFields / $maxScore) * 100;

        return (int) min(100, round($percentage));
    }

    /**
     * Get company logo URL with fallback.
     */
    public function getLogoUrlAttribute(): string
    {
        if ($this->company_logo) {
            // Check if it's already a full URL
            if (filter_var($this->company_logo, FILTER_VALIDATE_URL)) {
                return $this->company_logo;
            }

            // Check if it starts with 'storage/'
            if (strpos($this->company_logo, 'storage/') === 0) {
                return asset($this->company_logo);
            }

            // Otherwise, prepend storage path
            return asset('storage/' . $this->company_logo);
        }

        // Return default avatar
        return asset('assets/images/default-company-logo.png');
    }

}

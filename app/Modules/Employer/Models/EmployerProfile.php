<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class EmployerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'company_description',
        'industry',
        'company_size',
        'website',
        'company_logo',
        'location',
        'social_links',
        'status',
        'profile_views',
        'company_culture',
        'benefits_offered',
        'founded_year',
        'headquarters',
        'specialties',
        'company_video',
        'gallery_images',
        'hiring_process',
        'contact_email',
        'contact_phone',
        'meta_title',
        'meta_description',
        'total_jobs_posted',
        'active_jobs',
        'total_applications_received'
    ];

    protected $casts = [
        'social_links' => 'array',
        'profile_views' => 'integer',
        'company_culture' => 'array',
        'benefits_offered' => 'array',
        'specialties' => 'array',
        'gallery_images' => 'array',
        'hiring_process' => 'array',
        'founded_year' => 'integer',
        'total_jobs_posted' => 'integer',
        'active_jobs' => 'integer',
        'total_applications_received' => 'integer'
    ];

    protected $attributes = [
        'profile_views' => 0,
        'status' => 'draft',
        'social_links' => '{}',
        'company_culture' => '{}',
        'benefits_offered' => '{}',
        'specialties' => '{}',
        'gallery_images' => '[]',
        'hiring_process' => '[]',
        'total_jobs_posted' => 0,
        'active_jobs' => 0,
        'total_applications_received' => 0
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class, 'employer_id', 'user_id');
    }

    public function activeJobs(): HasMany
    {
        return $this->jobs()->where('status', 1);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class, 'employer_id', 'user_id');
    }

    public function recentApplications($limit = 5)
    {
        return $this->applications()
            ->with(['job', 'user'])
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();
    }

    public function getLogoUrlAttribute()
    {
        if ($this->company_logo) {
            // Check if the logo path is a full URL
            if (filter_var($this->company_logo, FILTER_VALIDATE_URL)) {
                return $this->company_logo;
            }
            
            // Check if the logo path already starts with storage/
            if (str_starts_with($this->company_logo, 'storage/')) {
                return asset($this->company_logo);
            }
            
            // Otherwise, assume it's a relative path in the storage disk
            return asset('storage/' . $this->company_logo);
        }
        return null;
    }

    public function getCompanyVideoUrlAttribute()
    {
        if ($this->company_video) {
            // Only return the URL if it's a valid URL
            if (filter_var($this->company_video, FILTER_VALIDATE_URL)) {
                return $this->company_video;
            }
            return Storage::disk('public')->url($this->company_video);
        }
        return null;
    }

    public function getGalleryImagesUrlAttribute()
    {
        if (empty($this->gallery_images)) {
            return [];
        }
        return array_map(function($image) {
            // Check if the image path already starts with storage/
            if (str_starts_with($image, 'storage/')) {
                return asset($image);
            }
            return Storage::disk('public')->url($image);
        }, $this->gallery_images);
    }

    public function incrementProfileViews()
    {
        $this->increment('profile_views');
    }

    public function updateJobMetrics()
    {
        $this->update([
            'total_jobs_posted' => $this->jobs()->count(),
            'active_jobs' => $this->activeJobs()->count(),
            'total_applications_received' => $this->applications()->count()
        ]);
    }

    public function getJobMetrics($range = 'month')
    {
        $startDate = match($range) {
            'week' => now()->subWeek(),
            'year' => now()->subYear(),
            default => now()->subMonth(),
        };

        // Get jobs count
        $totalJobs = $this->jobs()->count();
        $activeJobs = $this->activeJobs()->count();

        // Get applications count
        $totalApplications = $this->applications()
            ->where('created_at', '>=', $startDate)
            ->count();

        // Get profile views
        $profileViews = $this->profile_views;
        $recentProfileViews = JobView::whereIn('job_id', $this->jobs->pluck('id'))
            ->where('created_at', '>=', $startDate)
            ->count();

        return [
            'total_jobs' => $totalJobs,
            'active_jobs' => $activeJobs,
            'total_applications' => $totalApplications,
            'profile_views' => $profileViews,
            'recent_profile_views' => $recentProfileViews,
            'range' => $range
        ];
    }

    public function getProfileCompletionPercentage()
    {
        $fields = [
            'company_name',
            'company_description',
            'industry',
            'company_size',
            'location',
            'company_logo',
            'website',
            'social_links',
            'company_culture',
            'benefits_offered',
            'founded_year',
            'headquarters',
            'specialties',
            'contact_email',
            'contact_phone'
        ];

        $filledFields = collect($fields)->filter(function($field) {
            return !empty($this->$field);
        })->count();

        return round(($filledFields / count($fields)) * 100);
    }

    public function isPublished()
    {
        return $this->status === 'published';
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function getMetaTitle()
    {
        return $this->meta_title ?? $this->company_name . ' - Company Profile';
    }

    public function getMetaDescription()
    {
        return $this->meta_description ?? substr(strip_tags($this->company_description), 0, 160);
    }
} 
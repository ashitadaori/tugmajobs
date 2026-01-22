<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jobseeker extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'middle_name',
        'date_of_birth',
        'gender',
        'nationality',
        'marital_status',
        'phone',
        'alternate_phone',
        'linkedin_url',
        'portfolio_url',
        'github_url',
        'current_address',
        'permanent_address',
        'city',
        'state',
        'country',
        'postal_code',
        'current_job_title',
        'current_company',
        'professional_summary',
        'total_experience_years',
        'total_experience_months',
        'experience_level',
        'skills',
        'soft_skills',
        'languages',
        'certifications',
        'education',
        'courses',
        'work_experience',
        'projects',
        'preferred_job_types',
        'preferred_categories',
        'preferred_locations',
        'open_to_remote',
        'open_to_relocation',
        'expected_salary_min',
        'expected_salary_max',
        'salary_currency',
        'salary_period',
        'availability',
        'available_from',
        'currently_employed',
        'notice_period_days',
        'resume_file',
        'cover_letter_file',
        'profile_photo',
        'portfolio_files',
        'notification_preferences',
        'privacy_settings',
        'profile_visibility',
        'allow_recruiter_contact',
        'job_alert_preferences',
        'facebook_url',
        'twitter_url',
        'instagram_url',
        'profile_status',
        'is_featured',
        'is_premium',
        'premium_expires_at',
        'profile_completion_percentage',
        'profile_views',
        'total_applications',
        'interviews_attended',
        'jobs_offered',
        'average_rating',
        'search_keywords',
        'search_score',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'open_to_remote' => 'boolean',
        'open_to_relocation' => 'boolean',
        'expected_salary_min' => 'decimal:2',
        'expected_salary_max' => 'decimal:2',
        'available_from' => 'date',
        'currently_employed' => 'boolean',
        'notice_period_days' => 'integer',
        'skills' => 'array',
        'soft_skills' => 'array',
        'languages' => 'array',
        'certifications' => 'array',
        'education' => 'array',
        'courses' => 'array',
        'work_experience' => 'array',
        'projects' => 'array',
        'preferred_job_types' => 'array',
        'preferred_categories' => 'array',
        'preferred_locations' => 'array',
        'portfolio_files' => 'array',
        'notification_preferences' => 'array',
        'privacy_settings' => 'array',
        'job_alert_preferences' => 'array',
        'profile_visibility' => 'boolean',
        'allow_recruiter_contact' => 'boolean',
        'is_featured' => 'boolean',
        'is_premium' => 'boolean',
        'premium_expires_at' => 'datetime',
        'profile_completion_percentage' => 'decimal:2',
        'profile_views' => 'integer',
        'total_applications' => 'integer',
        'interviews_attended' => 'integer',
        'jobs_offered' => 'integer',
        'average_rating' => 'decimal:2',
        'search_score' => 'decimal:2',
    ];

    /**
     * Get the user that owns the jobseeker profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the job applications for this jobseeker.
     */
    public function jobApplications(): HasMany
    {
        return $this->hasMany(JobApplication::class, 'user_id', 'user_id');
    }

    /**
     * Get the saved jobs for this jobseeker.
     */
    public function savedJobs()
    {
        return $this->belongsToMany(Job::class, 'saved_jobs', 'user_id', 'job_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Get the full name of the jobseeker.
     */
    public function getFullNameAttribute(): string
    {
        $name = trim($this->first_name . ' ' . $this->last_name);
        return $name ?: $this->user->name ?? 'Unknown';
    }

    /**
     * Get the display name (with middle name if available).
     */
    public function getDisplayNameAttribute(): string
    {
        $parts = array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name
        ]);

        $name = implode(' ', $parts);
        return $name ?: $this->user->name ?? 'Unknown';
    }

    /**
     * Calculate age from date of birth.
     */
    public function getAgeAttribute(): ?int
    {
        if (!$this->date_of_birth) {
            return null;
        }

        return $this->date_of_birth->age;
    }

    /**
     * Get total experience in a readable format.
     */
    public function getTotalExperienceAttribute(): string
    {
        $years = $this->total_experience_years;
        $months = $this->total_experience_months;

        if ($years == 0 && $months == 0) {
            return 'Fresh Graduate';
        }

        $experience = [];
        if ($years > 0) {
            $experience[] = $years . ' year' . ($years > 1 ? 's' : '');
        }
        if ($months > 0) {
            $experience[] = $months . ' month' . ($months > 1 ? 's' : '');
        }

        return implode(' ', $experience);
    }

    /**
     * Get salary range in a readable format.
     */
    public function getSalaryRangeAttribute(): ?string
    {
        if (!$this->expected_salary_min && !$this->expected_salary_max) {
            return null;
        }

        $currency = $this->salary_currency;
        $period = ucfirst($this->salary_period);

        if ($this->expected_salary_min && $this->expected_salary_max) {
            return "{$currency} " . number_format($this->expected_salary_min) . " - " .
                number_format($this->expected_salary_max) . " / {$period}";
        } elseif ($this->expected_salary_min) {
            return "{$currency} " . number_format($this->expected_salary_min) . "+ / {$period}";
        } else {
            return "{$currency} " . number_format($this->expected_salary_max) . " / {$period}";
        }
    }

    /**
     * Check if the profile is complete.
     */
    public function isProfileComplete(): bool
    {
        return $this->profile_status === 'complete' || $this->profile_status === 'verified';
    }

    /**
     * Check if the profile is verified.
     */
    public function isVerified(): bool
    {
        return $this->profile_status === 'verified';
    }

    /**
     * Check if the jobseeker is currently available for hire.
     */
    public function isAvailable(): bool
    {
        if ($this->available_from && $this->available_from->isFuture()) {
            return false;
        }

        return $this->profile_visibility && $this->profile_status !== 'suspended';
    }

    /**
     * Check if the jobseeker has premium subscription.
     */
    public function isPremium(): bool
    {
        return $this->is_premium &&
            ($this->premium_expires_at === null || $this->premium_expires_at->isFuture());
    }

    /**
     * Get the profile photo URL.
     */
    public function getProfilePhotoUrlAttribute(): ?string
    {
        if ($this->profile_photo) {
            return asset('storage/' . $this->profile_photo);
        }

        // Fallback to user's profile image
        return $this->user->profile_image ?? null;
    }

    /**
     * Get the resume URL.
     */
    public function getResumeUrlAttribute(): ?string
    {
        if ($this->resume_file) {
            return asset('storage/' . $this->resume_file);
        }

        return null;
    }

    /**
     * Calculate and update profile completion percentage.
     */
    /**
     * Calculate and update profile completion percentage.
     */
    public function calculateProfileCompletion(): float
    {
        $user = $this->user;
        $score = 0;

        // 1. Basic Information (20 points)
        // Name (5)
        if (!empty($user->name))
            $score += 5;
        // Email (5)
        if (!empty($user->email))
            $score += 5;
        // Phone (5)
        if (!empty($user->phone))
            $score += 5;
        // Profile Image (5)
        if (!empty($user->image))
            $score += 5;

        // 2. Professional Title (5 points)
        if (!empty($this->current_job_title))
            $score += 5;

        // 3. Professional Summary (10 points) - Must be at least 100 chars
        if (!empty($this->professional_summary) && strlen($this->professional_summary) >= 100)
            $score += 10;

        // 4. Skills (10 points) - Must have at least 3
        if (is_array($this->skills) && count($this->skills) >= 3)
            $score += 10;

        // 5. Work Experience (8 points) - At least one entry
        if (is_array($this->work_experience) && count($this->work_experience) > 0)
            $score += 8;

        // 6. Education (7 points) - At least one entry
        if (is_array($this->education) && count($this->education) > 0)
            $score += 7;

        // 7. Resume (10 points) - Either in profile or uploaded
        $hasResume = !empty($this->resume_file) || ($user && $user->resumes()->count() > 0);
        if ($hasResume)
            $score += 10;

        // 8. Job Preferences (15 points)
        // Categories (5)
        if (is_array($this->preferred_categories) && count($this->preferred_categories) > 0)
            $score += 5;
        // Job Types (5)
        if (is_array($this->preferred_job_types) && count($this->preferred_job_types) > 0)
            $score += 5;
        // Experience Level (5)
        if (!empty($this->experience_level))
            $score += 5;

        // 9. KYC Verification (15 points)
        if ($user && $user->kyc_status === 'verified')
            $score += 15;

        $percentage = min(100, max(0, $score));

        // Update the database
        $this->update(['profile_completion_percentage' => $percentage]);

        return $percentage;
    }

    /**
     * Update profile status based on completion percentage.
     */
    public function updateProfileStatus(): void
    {
        $completion = $this->calculateProfileCompletion();

        if ($completion >= 80) {
            $this->update(['profile_status' => 'complete']);
        } elseif ($completion < 30) {
            $this->update(['profile_status' => 'incomplete']);
        }
    }

    /**
     * Increment profile views counter.
     */
    public function incrementViews(): void
    {
        $this->increment('profile_views');
    }

    /**
     * Get matching jobs based on preferences.
     */
    public function getMatchingJobs()
    {
        $query = Job::where('status', 'active');

        // Filter by preferred categories
        if ($this->preferred_categories) {
            $query->whereIn('category_id', $this->preferred_categories);
        }

        // Filter by preferred locations
        if ($this->preferred_locations) {
            $query->where(function ($q) {
                foreach ($this->preferred_locations as $location) {
                    $q->orWhere('location', 'like', "%{$location}%");
                }
            });
        }

        // Filter by salary range
        if ($this->expected_salary_min) {
            $query->where('salary_max', '>=', $this->expected_salary_min);
        }

        // Filter by job types
        if ($this->preferred_job_types) {
            $query->whereIn('job_type', $this->preferred_job_types);
        }

        // Include remote jobs if open to remote
        if ($this->open_to_remote) {
            $query->orWhere('is_remote', true);
        }

        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Get skills as a comma-separated string.
     */
    public function getSkillsStringAttribute(): string
    {
        if (!$this->skills) {
            return '';
        }

        // Handle both array and string formats
        if (is_array($this->skills)) {
            return implode(', ', $this->skills);
        }

        // If it's already a string, return it
        return $this->skills;
    }

    /**
     * Get education summary.
     */
    public function getEducationSummaryAttribute(): string
    {
        if (!$this->education || empty($this->education)) {
            return 'Not specified';
        }

        $latest = collect($this->education)->sortByDesc('year')->first();
        return $latest['degree'] ?? 'Education completed';
    }

    /**
     * Check if jobseeker matches job requirements.
     */
    public function matchesJob(Job $job): array
    {
        $matches = [];
        $score = 0;

        // Salary match
        if ($job->salary_min && $this->expected_salary_max) {
            if ($job->salary_min <= $this->expected_salary_max) {
                $matches['salary'] = true;
                $score += 20;
            }
        }

        // Location match
        if ($this->open_to_remote && $job->is_remote) {
            $matches['location'] = true;
            $score += 15;
        } elseif (in_array($job->location, $this->preferred_locations ?? [])) {
            $matches['location'] = true;
            $score += 15;
        }

        // Skills match
        if ($job->required_skills && $this->skills) {
            $jobSkills = $job->required_skills;
            $candidateSkills = $this->skills;
            $commonSkills = array_intersect($jobSkills, $candidateSkills);

            if (!empty($commonSkills)) {
                $matches['skills'] = $commonSkills;
                $score += (count($commonSkills) / count($jobSkills)) * 30;
            }
        }

        // Experience match
        if ($job->min_experience && $this->total_experience_years >= $job->min_experience) {
            $matches['experience'] = true;
            $score += 20;
        }

        // Job type match
        if (in_array($job->job_type, $this->preferred_job_types ?? [])) {
            $matches['job_type'] = true;
            $score += 15;
        }

        return [
            'matches' => $matches,
            'score' => round($score, 2),
            'is_good_match' => $score >= 60
        ];
    }
}

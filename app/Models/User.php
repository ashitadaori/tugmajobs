<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use App\Models\SavedJob;
use App\Models\BookmarkedJob;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // Authentication & Basic Info
        'name',
        'email',
        'password',
        'role',
        'email_verified_at',

        // Contact
        'phone',

        // Profile Images
        'image',
        'profile_image',

        // Account Status
        'is_active',

        // KYC Fields
        'kyc_status',
        'kyc_session_id',
        'kyc_completed_at',
        'kyc_verified_at',
        'kyc_data',

        // Social Authentication
        'google_id',
        'google_token',
        'google_refresh_token',

        // Settings
        'notification_preferences',
        'privacy_settings',

        // Two-Factor Authentication
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'notification_preferences' => 'array',
        'privacy_settings' => 'array',
        'is_active' => 'boolean',
        'kyc_completed_at' => 'datetime',
        'kyc_verified_at' => 'datetime',
        'kyc_data' => 'array',
        'deleted_at' => 'datetime',
        'two_factor_enabled' => 'boolean',
        'two_factor_confirmed_at' => 'datetime',
    ];

    /**
     * Get the jobs posted by the user.
     */
    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class, 'employer_id');
    }

    /**
     * Get the job applications submitted by the user.
     */
    public function jobApplications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    /**
     * Get the jobs saved by the user.
     */
    public function savedJobs(): HasMany
    {
        return $this->hasMany(SavedJob::class);
    }

    /**
     * Get the jobs bookmarked by the user (alias for savedJobs).
     */
    public function bookmarkedJobs(): HasMany
    {
        return $this->hasMany(BookmarkedJob::class);
    }

    /**
     * Get the bookmarked jobs with job details.
     */
    public function bookmarkedJobsWithDetails(): HasMany
    {
        return $this->hasMany(BookmarkedJob::class)->with(['job', 'job.employer', 'job.jobType', 'job.category']);
    }

    /**
     * Get the KYC verifications for the user.
     */
    public function kycVerifications(): HasMany
    {
        return $this->hasMany(KycVerification::class);
    }

    /**
     * Get the latest KYC verification for the user.
     */
    public function latestKycVerification()
    {
        return $this->hasOne(KycVerification::class)->latest();
    }



    // Profile relationships - using new table structure
    public function jobSeekerProfile(): HasOne
    {
        return $this->hasOne(Jobseeker::class);
    }

    public function employerProfile(): HasOne
    {
        return $this->hasOne(Employer::class);
    }


    /**
     * Get the KYC data for the user.
     */
    public function kycData(): HasMany
    {
        return $this->hasMany(KycData::class);
    }

    /**
     * Get the latest KYC data for the user.
     */
    public function latestKycData()
    {
        return $this->hasOne(KycData::class)->latest();
    }

    /**
     * Get the employer profile for the user.
     */
    public function employer(): HasOne
    {
        return $this->hasOne(Employer::class);
    }

    /**
     * Get the jobseeker profile for the user.
     */
    public function jobseeker(): HasOne
    {
        return $this->hasOne(Jobseeker::class);
    }

    /**
     * Get the resumes for the user.
     */
    public function resumes(): HasMany
    {
        return $this->hasMany(Resume::class);
    }

    /**
     * Get the employer documents for the user.
     */
    public function employerDocuments(): HasMany
    {
        return $this->hasMany(EmployerDocument::class);
    }

    // Role checks using the simple role column (roles/permissions tables were removed)
    public function hasRole($roleName)
    {
        return $this->role === strtolower($roleName);
    }

    // Role checks using the simple role column
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('superadmin') || $this->role === 'superadmin';
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || $this->role === 'admin' || $this->isSuperAdmin();
    }

    public function isEmployer(): bool
    {
        return $this->hasRole('employer') || $this->role === 'employer';
    }

    public function isJobSeeker(): bool
    {
        return $this->hasRole('jobseeker') || $this->role === 'jobseeker';
    }

    public function isKycVerified(): bool
    {
        return $this->kyc_status === 'verified';
    }

    public function isKycPending(): bool
    {
        return $this->kyc_status === 'pending';
    }

    public function isKycInProgress(): bool
    {
        return $this->kyc_status === 'in_progress';
    }

    public function isKycFailed(): bool
    {
        return $this->kyc_status === 'failed';
    }

    public function isKycPendingReview(): bool
    {
        return $this->kyc_status === 'pending_review';
    }

    public function getKycStatusBadgeAttribute(): string
    {
        $badges = [
            'pending' => '<span class="badge bg-secondary"><i class="fas fa-clock me-1"></i>Not Verified</span>',
            'in_progress' => '<span class="badge bg-warning"><i class="fas fa-hourglass-half me-1"></i>In Progress</span>',
            'verified' => '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Verified</span>',
            'failed' => '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Failed</span>',
            'expired' => '<span class="badge bg-dark"><i class="fas fa-clock me-1"></i>Expired</span>',
            'pending_review' => '<span class="badge bg-info"><i class="fas fa-hourglass-half me-1"></i>Pending Review</span>'
        ];
        return $badges[$this->kyc_status] ?? $badges['pending'];
    }

    public function getKycStatusTextAttribute(): string
    {
        $statuses = [
            'pending' => 'Not Verified',
            'in_progress' => 'Verification in Progress',
            'verified' => 'Verified',
            'failed' => 'Verification Failed',
            'expired' => 'Verification Expired',
            'pending_review' => 'Pending Manual Review'
        ];
        return $statuses[$this->kyc_status] ?? 'Not Verified';
    }

    public function getVerifiedBadgeAttribute(): string
    {
        if ($this->isKycVerified()) {
            return '<i class="fas fa-check-circle text-success ms-1" title="Verified Profile" data-bs-toggle="tooltip"></i>';
        }
        return '';
    }

    public function needsKycVerification(): bool
    {
        return in_array($this->kyc_status, ['pending', 'failed', 'expired']);
    }

    public function canStartKycVerification(): bool
    {
        // Allow starting verification for these statuses
        if (in_array($this->kyc_status, ['pending', 'failed', 'expired'])) {
            return true;
        }

        // 'pending_review' means it is already submitted, so they cannot start again until verified or failed.
        if ($this->kyc_status === 'pending_review') {
            return false;
        }

        // Allow restarting if in_progress but session is old (more than 30 minutes)
        // Extended timeout to give users more time to complete verification
        if ($this->kyc_status === 'in_progress' && $this->updated_at) {
            $thirtyMinutesAgo = now()->subMinutes(30);
            if ($this->updated_at->lt($thirtyMinutesAgo)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the current KYC session has timed out
     */
    public function hasKycSessionTimedOut(): bool
    {
        if ($this->kyc_status !== 'in_progress' || !$this->updated_at) {
            return false;
        }

        $thirtyMinutesAgo = now()->subMinutes(30);
        return $this->updated_at->lt($thirtyMinutesAgo);
    }

    /**
     * Reset expired KYC session back to pending
     */
    public function resetExpiredKycSession(): bool
    {
        if ($this->hasKycSessionTimedOut()) {
            $this->update([
                'kyc_status' => 'pending',
                'kyc_session_id' => null,
            ]);
            return true;
        }
        return false;
    }

    // Messaging
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    // Audit logs
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Get the user's notifications.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the user's unread notifications count.
     */
    public function getUnreadNotificationsCountAttribute(): int
    {
        return $this->notifications()->whereNull('read_at')->count();
    }

    // Google Authentication Helper Methods
    public function hasGoogleAccount(): bool
    {
        return !empty($this->google_id);
    }

    public function getConnectedSocialProviders(): array
    {
        $providers = [];

        if ($this->hasGoogleAccount()) {
            $providers[] = 'google';
        }

        return $providers;
    }

    public function getProfileImageAttribute($value): ?string
    {
        // Return custom uploaded image
        if (!empty($this->image)) {
            // Check for thumbnail first (generated by AccountController)
            if (file_exists(public_path('profile_img/thumb/' . $this->image))) {
                return asset('profile_img/thumb/' . $this->image);
            }
            // Check for original image
            if (file_exists(public_path('profile_img/' . $this->image))) {
                return asset('profile_img/' . $this->image);
            }
            // Fallback to storage (legacy)
            return asset('storage/' . $this->image);
        }

        // Return social profile image if no custom image is set
        if (empty($value)) {
            if ($this->hasGoogleAccount()) {
                return $this->attributes['profile_image'] ?? null;
            }
        }

        // Return social profile image
        return $value;
    }

    public function isSocialUser(): bool
    {
        return $this->hasGoogleAccount();
    }

    /**
     * Check if employer has all required documents approved.
     */
    public function hasRequiredDocumentsApproved(): bool
    {
        if (!$this->isEmployer()) {
            return false;
        }

        $requiredTypes = collect(EmployerDocument::getDocumentTypes())
            ->filter(fn($config) => $config['required'])
            ->keys();

        foreach ($requiredTypes as $type) {
            $hasApprovedDocument = $this->employerDocuments()
                ->where('document_type', $type)
                ->where('status', 'approved')
                ->exists();

            if (!$hasApprovedDocument) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if employer can post jobs.
     *
     * Supports three modes:
     * 1. KYC Only: Only KYC verification required (EMPLOYER_KYC_ONLY=true)
     * 2. Full Verification: KYC + Document approval (default, EMPLOYER_KYC_ONLY=false)
     * 3. Disabled: No checks (testing only, DISABLE_KYC_FOR_EMPLOYERS=true)
     */
    public function canPostJobs(): bool
    {
        if (!$this->isEmployer()) {
            return false;
        }

        // Mode 1: All checks disabled (testing only)
        if (config('app.disable_kyc_for_employers', false)) {
            return true;
        }

        // Mode 2: KYC-only mode (like jobseekers)
        if (config('app.employer_kyc_only', false)) {
            return $this->isKycVerified();
        }

        // Mode 3: Full verification (default)
        // Requires both KYC and approved documents
        return $this->isKycVerified() && $this->hasRequiredDocumentsApproved();
    }

    /**
     * Get employer verification status.
     *
     * Returns different status based on verification mode.
     */
    public function getEmployerVerificationStatus(): array
    {
        if (!$this->isEmployer()) {
            return ['status' => 'not_employer', 'message' => 'Not an employer'];
        }

        // Check if all checks are disabled
        if (config('app.disable_kyc_for_employers', false)) {
            return ['status' => 'verified', 'message' => 'Verification disabled for testing'];
        }

        $kycVerified = $this->isKycVerified();

        // KYC-only mode (like jobseekers)
        if (config('app.employer_kyc_only', false)) {
            if ($kycVerified) {
                return ['status' => 'verified', 'message' => 'KYC verified - can post jobs'];
            } else {
                return ['status' => 'kyc_pending', 'message' => 'KYC verification required'];
            }
        }

        // Full verification mode (default)
        $documentsApproved = $this->hasRequiredDocumentsApproved();

        if ($kycVerified && $documentsApproved) {
            return ['status' => 'verified', 'message' => 'Fully verified - can post jobs'];
        } elseif (!$kycVerified) {
            return ['status' => 'kyc_pending', 'message' => 'KYC verification required'];
        } else {
            return ['status' => 'documents_pending', 'message' => 'Document approval required'];
        }
    }
}

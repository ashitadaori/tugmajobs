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
        'name',
        'email',
        'password',
        'role',
        'mobile',
        'designation',
        'image',
        'skills',
        'education',
        'experience_years',
        'bio',
        'is_active',
        'kyc_status',
        'kyc_session_id',
        'kyc_completed_at',
        'kyc_verified_at',
        'kyc_data'
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
        'skills' => 'array',
        'education' => 'array',
        'preferred_job_types' => 'array',
        'preferred_categories' => 'array',
        'notification_preferences' => 'array',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'salary' => 'decimal:2',
        'categories' => 'string',
        'kyc_completed_at' => 'datetime',
        'kyc_verified_at' => 'datetime',
        'kyc_data' => 'array'
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
    public function savedJobs()
    {
        return $this->belongsToMany(Job::class, 'saved_jobs')
            ->withTimestamps();
    }

    // Profile relationships
    public function jobSeekerProfile(): HasOne
    {
        return $this->hasOne(JobSeekerProfile::class);
    }

    public function employerProfile(): HasOne
    {
        return $this->hasOne(EmployerProfile::class);
    }

    public function kycDocuments(): HasMany
    {
        return $this->hasMany(KycDocument::class);
    }

    // Role and Permission relationships
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function hasRole($roleName)
    {
        // Check both the role column and the roles relationship
        return $this->role === strtolower($roleName) || 
               $this->roles()->where('name', $roleName)->exists();
    }

    public function hasPermission($permissionName)
    {
        return $this->roles()
            ->whereHas('permissions', function($query) use ($permissionName) {
                $query->where('name', $permissionName);
            })->exists();
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

    public function getKycStatusBadgeAttribute(): string
    {
        $badges = [
            'pending' => '<span class="badge bg-secondary"><i class="fas fa-clock me-1"></i>Not Verified</span>',
            'in_progress' => '<span class="badge bg-warning"><i class="fas fa-hourglass-half me-1"></i>In Progress</span>',
            'verified' => '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Verified</span>',
            'failed' => '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Failed</span>',
            'expired' => '<span class="badge bg-dark"><i class="fas fa-clock me-1"></i>Expired</span>'
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
            'expired' => 'Verification Expired'
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
        return in_array($this->kyc_status, ['pending', 'failed', 'expired']);
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
}

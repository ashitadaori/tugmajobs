<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobApplication extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'job_id',
        'user_id',
        'employer_id',
        'status',
        'shortlisted',
        'cover_letter',
        'resume',
        'notes',
        'applied_date',
        'preliminary_answers',
        'application_step',
        'profile_updated',
        // New stage-related fields
        'stage',
        'stage_status',
        'submitted_documents',
        'interview_date',
        'interview_time',
        'interview_location',
        'interview_type',
        'interview_notes',
        'interview_scheduled_at'
    ];

    protected $casts = [
        'applied_date' => 'datetime',
        'preliminary_answers' => 'array',
        'profile_updated' => 'boolean',
        'submitted_documents' => 'array',
        'interview_date' => 'date',
        'interview_scheduled_at' => 'datetime'
    ];

    // Status constants (legacy - for backward compatibility)
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    // Stage constants
    const STAGE_APPLICATION = 'application';
    const STAGE_REQUIREMENTS = 'requirements';
    const STAGE_INTERVIEW = 'interview';
    const STAGE_HIRED = 'hired';
    const STAGE_REJECTED = 'rejected';

    // Stage status constants
    const STAGE_STATUS_PENDING = 'pending';
    const STAGE_STATUS_APPROVED = 'approved';
    const STAGE_STATUS_REJECTED = 'rejected';

    // Interview type constants
    const INTERVIEW_IN_PERSON = 'in_person';
    const INTERVIEW_VIDEO_CALL = 'video_call';
    const INTERVIEW_PHONE = 'phone';

    // Relationships
    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function employer()
    {
        return $this->belongsTo(User::class, 'employer_id');
    }

    public function statusHistory()
    {
        return $this->hasMany(ApplicationStatusHistory::class);
    }

    // Scopes
    public function scopeByEmployer($query, $employerId)
    {
        return $query->where('employer_id', $employerId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByStage($query, $stage)
    {
        return $query->where('stage', $stage);
    }

    public function scopeByStageStatus($query, $stageStatus)
    {
        return $query->where('stage_status', $stageStatus);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // Methods
    public function updateStatus($newStatus, $notes = null)
    {
        $oldStatus = $this->status;
        $this->status = $newStatus;
        $this->save();

        // Record status history
        $this->statusHistory()->create([
            'status' => $newStatus,
            'notes' => $notes ?? 'Status updated to ' . ucfirst($newStatus)
        ]);

        // Notify the applicant
        if ($oldStatus !== $newStatus) {
            $this->user->notify(new \App\Notifications\ApplicationStatusUpdated($this));
        }
    }

    /**
     * Get human-readable stage name
     */
    public function getStageName(): string
    {
        $stages = [
            self::STAGE_APPLICATION => 'Application Review',
            self::STAGE_REQUIREMENTS => 'Document Submission',
            self::STAGE_INTERVIEW => 'Interview',
            self::STAGE_HIRED => 'Hired',
            self::STAGE_REJECTED => 'Rejected'
        ];

        return $stages[$this->stage] ?? 'Unknown';
    }

    /**
     * Get stage badge class for UI
     */
    public function getStageBadgeClass(): string
    {
        $classes = [
            self::STAGE_APPLICATION => 'bg-info',
            self::STAGE_REQUIREMENTS => 'bg-warning',
            self::STAGE_INTERVIEW => 'bg-primary',
            self::STAGE_HIRED => 'bg-success',
            self::STAGE_REJECTED => 'bg-danger'
        ];

        return $classes[$this->stage] ?? 'bg-secondary';
    }

    /**
     * Get stage status badge class for UI
     */
    public function getStageStatusBadgeClass(): string
    {
        $classes = [
            self::STAGE_STATUS_PENDING => 'bg-warning',
            self::STAGE_STATUS_APPROVED => 'bg-success',
            self::STAGE_STATUS_REJECTED => 'bg-danger'
        ];

        return $classes[$this->stage_status] ?? 'bg-secondary';
    }

    /**
     * Get interview type display name
     */
    public function getInterviewTypeName(): string
    {
        $types = [
            self::INTERVIEW_IN_PERSON => 'In Person',
            self::INTERVIEW_VIDEO_CALL => 'Video Call',
            self::INTERVIEW_PHONE => 'Phone Call'
        ];

        return $types[$this->interview_type] ?? 'Not Set';
    }

    /**
     * Check if application can advance to next stage
     */
    public function canAdvanceStage(): bool
    {
        // Can only advance if current stage status is approved
        if ($this->stage_status !== self::STAGE_STATUS_APPROVED) {
            return false;
        }

        // Check stage progression
        $validTransitions = [
            self::STAGE_APPLICATION => self::STAGE_REQUIREMENTS,
            self::STAGE_REQUIREMENTS => self::STAGE_INTERVIEW,
            self::STAGE_INTERVIEW => self::STAGE_HIRED
        ];

        return isset($validTransitions[$this->stage]);
    }

    /**
     * Get the next stage
     */
    public function getNextStage(): ?string
    {
        $transitions = [
            self::STAGE_APPLICATION => self::STAGE_REQUIREMENTS,
            self::STAGE_REQUIREMENTS => self::STAGE_INTERVIEW,
            self::STAGE_INTERVIEW => self::STAGE_HIRED
        ];

        return $transitions[$this->stage] ?? null;
    }

    /**
     * Advance to next stage
     */
    public function advanceToNextStage(string $notes = null): bool
    {
        if (!$this->canAdvanceStage()) {
            return false;
        }

        $nextStage = $this->getNextStage();
        if (!$nextStage) {
            return false;
        }

        $this->stage = $nextStage;
        $this->stage_status = self::STAGE_STATUS_PENDING;
        $this->save();

        // Record in status history
        $this->statusHistory()->create([
            'status' => $nextStage,
            'notes' => $notes ?? 'Advanced to ' . $this->getStageName()
        ]);

        return true;
    }

    /**
     * Approve current stage
     * Auto-advances to 'requirements' stage when 'application' stage is approved
     */
    public function approveCurrentStage(string $notes = null): void
    {
        $this->stage_status = self::STAGE_STATUS_APPROVED;
        $this->save();

        $this->statusHistory()->create([
            'status' => $this->stage . '_approved',
            'notes' => $notes ?? $this->getStageName() . ' approved'
        ]);

        // Auto-advance from application stage to requirements stage
        // This allows jobseekers to immediately submit required documents
        if ($this->stage === self::STAGE_APPLICATION) {
            $this->stage = self::STAGE_REQUIREMENTS;
            $this->stage_status = self::STAGE_STATUS_PENDING;
            $this->save();

            $this->statusHistory()->create([
                'status' => 'advanced_to_requirements',
                'notes' => 'Auto-advanced to document submission stage'
            ]);
        }
    }

    /**
     * Reject application at current stage
     */
    public function rejectApplication(string $notes = null): void
    {
        $this->stage_status = self::STAGE_STATUS_REJECTED;
        $this->stage = self::STAGE_REJECTED;
        $this->status = self::STATUS_REJECTED;
        $this->save();

        $this->statusHistory()->create([
            'status' => 'rejected',
            'notes' => $notes ?? 'Application rejected'
        ]);

        // Note: Notification is handled by the controller to avoid duplicates
    }

    /**
     * Schedule interview
     */
    public function scheduleInterview(
        string $date,
        string $time,
        string $location,
        string $type,
        string $notes = null
    ): void {
        $this->interview_date = $date;
        $this->interview_time = $time;
        $this->interview_location = $location;
        $this->interview_type = $type;
        $this->interview_notes = $notes;
        $this->interview_scheduled_at = now();
        $this->save();

        $this->statusHistory()->create([
            'status' => 'interview_scheduled',
            'notes' => 'Interview scheduled for ' . $date . ' at ' . $time
        ]);
    }

    /**
     * Mark as hired
     */
    public function markAsHired(string $notes = null): void
    {
        $this->stage = self::STAGE_HIRED;
        $this->stage_status = self::STAGE_STATUS_APPROVED;
        $this->status = self::STATUS_APPROVED;
        $this->save();

        $this->statusHistory()->create([
            'status' => 'hired',
            'notes' => $notes ?? 'Applicant hired'
        ]);
    }

    /**
     * Check if requirements have been submitted
     */
    public function hasSubmittedRequirements(): bool
    {
        return !empty($this->submitted_documents);
    }

    /**
     * Check if interview is scheduled
     */
    public function hasScheduledInterview(): bool
    {
        return !empty($this->interview_date);
    }

    /**
     * Get the current stage progress percentage
     */
    public function getProgressPercentage(): int
    {
        $stages = [
            self::STAGE_APPLICATION => 25,
            self::STAGE_REQUIREMENTS => 50,
            self::STAGE_INTERVIEW => 75,
            self::STAGE_HIRED => 100,
            self::STAGE_REJECTED => 0
        ];

        return $stages[$this->stage] ?? 0;
    }

    /**
     * Check if application is in a specific stage
     */
    public function isInStage(string $stage): bool
    {
        return $this->stage === $stage;
    }

    /**
     * Check if application is still active (not rejected or hired)
     */
    public function isActive(): bool
    {
        return !in_array($this->stage, [self::STAGE_REJECTED, self::STAGE_HIRED]);
    }
}

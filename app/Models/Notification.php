<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'data',
        'read_at',
        'action_url'
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime'
    ];

    /**
     * Get the user that owns the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope a query to only include read notifications.
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Mark the notification as read.
     */
    public function markAsRead()
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Check if the notification is read.
     */
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    /**
     * Get the icon class based on notification type.
     */
    public function getIconClass(): string
    {
        $icons = [
            'job_application' => 'fas fa-file-alt text-primary',
            'job_saved' => 'fas fa-heart text-danger',
            'application_status' => 'fas fa-user text-success',
            'job_match' => 'fas fa-briefcase text-primary',
            'profile_view' => 'fas fa-eye text-info',
            'message' => 'fas fa-envelope text-warning',
            'system' => 'fas fa-bell text-secondary',
            // Admin notifications
            'admin_kyc_submission' => 'fas fa-id-card text-info',
            'admin_employer_document' => 'fas fa-file-upload text-warning',
            'new_job_pending' => 'fas fa-briefcase text-warning',
            'admin_new_application' => 'fas fa-file-alt text-info',
            // Employer/User notifications
            'document_approved' => 'fas fa-check-circle text-success',
            'document_rejected' => 'fas fa-times-circle text-danger',
            'kyc_verified' => 'fas fa-shield-alt text-success',
            'kyc_rejected' => 'fas fa-exclamation-triangle text-danger',
            'job_approved' => 'fas fa-check-circle text-success',
            'job_rejected' => 'fas fa-times-circle text-warning',
        ];

        return $icons[$this->type] ?? 'fas fa-bell text-secondary';
    }
}
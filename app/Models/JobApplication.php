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
        'status',
        'cover_letter',
        'resume',
        'notes',
        'applied_date'
    ];

    protected $casts = [
        'applied_date' => 'datetime'
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    // Relationships
    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function statusHistory()
    {
        return $this->hasMany(ApplicationStatusHistory::class);
    }

    // Scopes
    public function scopeByEmployer($query, $employerId)
    {
        return $query->whereHas('job', function($q) use ($employerId) {
            $q->where('employer_id', $employerId);
        });
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
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
}

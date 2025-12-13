<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'job_application_status_histories';

    protected $fillable = [
        'job_application_id',
        'status',
        'old_status',
        'new_status',
        'notes',
        'updated_by'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function jobApplication()
    {
        return $this->belongsTo(JobApplication::class);
    }

    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get human-readable description of the status change
     */
    public function getStatusChangeDescription(): string
    {
        // Handle cases where old_status/new_status might not be set
        if ($this->old_status && $this->new_status) {
            $description = sprintf(
                'Status changed from %s to %s',
                ucfirst($this->old_status),
                ucfirst($this->new_status)
            );
        } elseif ($this->status) {
            $description = sprintf('Status: %s', ucfirst($this->status));
        } else {
            $description = 'Status updated';
        }

        // Add who made the change if available
        if ($this->updatedByUser) {
            $description .= ' by ' . $this->updatedByUser->name;
        }

        return $description;
    }
} 
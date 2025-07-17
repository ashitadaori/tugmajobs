<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_application_id',
        'status',
        'notes'
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

    public function getStatusChangeDescription()
    {
        return sprintf(
            'Status changed from %s to %s by %s',
            ucfirst($this->old_status),
            ucfirst($this->new_status),
            $this->updatedByUser->name
        );
    }
} 
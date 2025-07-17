<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'location',
        'salary_range',
        'frequency',
        'email_notifications'
    ];

    protected $casts = [
        'email_notifications' => 'boolean',
        'salary_range' => 'float'
    ];

    /**
     * Get the user that owns the job alert.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the categories for the job alert.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'job_alert_categories');
    }

    /**
     * Get the job types for the job alert.
     */
    public function jobTypes()
    {
        return $this->belongsToMany(JobType::class, 'job_alert_job_types');
    }
} 
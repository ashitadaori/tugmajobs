<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobSeekerProfile extends Model
{
    protected $table = 'jobseekers';
    
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'phone',
        'city',
        'state',
        'country',
        'professional_summary',
        'skills',
        'education',
        'work_experience',
        'resume_file', // maps to 'resume' conceptually
        'expected_salary_min',
        'expected_salary_max',
        'preferred_locations', // maps to 'preferred_location' conceptually
        'profile_status',
        'profile_completion_percentage'
    ];

    protected $casts = [
        'skills' => 'array',
        'education' => 'array',
        'work_experience' => 'array',
        'preferred_locations' => 'array',
        'expected_salary_min' => 'decimal:2',
        'expected_salary_max' => 'decimal:2',
        'profile_completion_percentage' => 'decimal:2'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
} 
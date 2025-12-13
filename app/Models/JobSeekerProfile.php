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
        'alternate_phone',
        'city',
        'state',
        'country',
        'professional_summary',
        'current_job_title',
        'current_company',
        'current_salary',
        'current_salary_currency',
        'skills',
        'education',
        'work_experience',
        'total_experience_years',
        'total_experience_months',
        'resume_file',
        'expected_salary_min',
        'expected_salary_max',
        'preferred_job_types',
        'preferred_categories',
        'preferred_locations',
        'experience_level',
        'profile_status',
        'profile_completion_percentage',
        // Social Links
        'linkedin_url',
        'github_url',
        'portfolio_url',
        'facebook_url',
        'twitter_url',
        'instagram_url'
    ];

    protected $casts = [
        'skills' => 'array',
        'education' => 'array',
        'work_experience' => 'array',
        'preferred_job_types' => 'array',
        'preferred_categories' => 'array',
        'preferred_locations' => 'array',
        'expected_salary_min' => 'decimal:2',
        'expected_salary_max' => 'decimal:2',
        'current_salary' => 'decimal:2',
        'profile_completion_percentage' => 'decimal:2'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
} 
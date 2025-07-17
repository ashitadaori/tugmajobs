<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobSeekerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'resume',
        'skills',
        'experience',
        'education',
        'current_salary',
        'expected_salary',
        'preferred_location',
        'is_kyc_verified'
    ];

    protected $casts = [
        'is_kyc_verified' => 'boolean',
        'skills' => 'array',
        'education' => 'array'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
} 
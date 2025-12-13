<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResumeData extends Model
{
    use HasFactory;

    protected $fillable = [
        'resume_id',
        'personal_info',
        'professional_summary',
        'work_experience',
        'education',
        'skills',
        'certifications',
        'languages',
        'projects',
        'achievements',
    ];

    protected $casts = [
        'personal_info' => 'array',
        'work_experience' => 'array',
        'education' => 'array',
        'skills' => 'array',
        'certifications' => 'array',
        'languages' => 'array',
        'projects' => 'array',
        'achievements' => 'array',
    ];

    /**
     * Get the resume that owns this data
     */
    public function resume()
    {
        return $this->belongsTo(Resume::class);
    }
}

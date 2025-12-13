<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resume extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'template_id',
        'title',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Get the user that owns the resume
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the template used by this resume
     */
    public function template()
    {
        return $this->belongsTo(ResumeTemplate::class, 'template_id');
    }

    /**
     * Get the resume data
     */
    public function data()
    {
        return $this->hasOne(ResumeData::class);
    }
}

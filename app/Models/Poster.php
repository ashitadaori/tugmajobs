<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poster extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'created_by',
        'employer_id',
        'job_id',
        'job_title',
        'requirements',
        'company_name',
        'contact_email',
        'contact_phone',
        'location',
        'salary_range',
        'employment_type',
        'deadline',
        'company_logo',
        'primary_color',
        'secondary_color',
        'poster_type',
        // PosterMyWall fields
        'pmw_template_id',
        'pmw_design_id',
        'pmw_preview_url',
        'pmw_download_url',
        'pmw_customizations',
        'source', // 'local' or 'postermywall'
    ];

    protected $casts = [
        'deadline' => 'date',
        'pmw_customizations' => 'array',
    ];

    /**
     * Get the template used for this poster
     */
    public function template()
    {
        return $this->belongsTo(PosterTemplate::class, 'template_id');
    }

    /**
     * Get the admin who created this poster
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the employer who created this poster
     */
    public function employer()
    {
        return $this->belongsTo(User::class, 'employer_id');
    }

    /**
     * Get the job linked to this poster
     */
    public function job()
    {
        return $this->belongsTo(Job::class, 'job_id');
    }

    /**
     * Scope for admin posters
     */
    public function scopeAdminPosters($query)
    {
        return $query->where('poster_type', 'admin');
    }

    /**
     * Scope for employer posters
     */
    public function scopeEmployerPosters($query)
    {
        return $query->where('poster_type', 'employer');
    }

    /**
     * Scope for posters by specific employer
     */
    public function scopeByEmployer($query, $employerId)
    {
        return $query->where('employer_id', $employerId);
    }

    /**
     * Get the company logo URL
     */
    public function getLogoUrlAttribute()
    {
        if ($this->company_logo) {
            return asset('storage/' . $this->company_logo);
        }

        // Try to get from employer profile
        if ($this->employer_id) {
            $employer = Employer::where('user_id', $this->employer_id)->first();
            if ($employer && $employer->company_logo) {
                return $employer->logo_url;
            }
        }

        return null;
    }

    /**
     * Check if poster is from PosterMyWall
     */
    public function isPosterMyWall(): bool
    {
        return $this->source === 'postermywall' || !empty($this->pmw_design_id);
    }

    /**
     * Scope for PosterMyWall posters
     */
    public function scopePosterMyWall($query)
    {
        return $query->where('source', 'postermywall');
    }

    /**
     * Scope for local posters
     */
    public function scopeLocal($query)
    {
        return $query->where(function ($q) {
            $q->where('source', 'local')->orWhereNull('source');
        });
    }
}

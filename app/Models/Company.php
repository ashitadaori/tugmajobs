<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\LogsAudit;

class Company extends Model
{
    use HasFactory, SoftDeletes, LogsAudit;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'website',
        'logo',
        'description',
        'industry',
        'company_size',
        'founded_year',
        'location',
        'latitude',
        'longitude',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($company) {
            if (empty($company->slug)) {
                $company->slug = Str::slug($company->name);
            }
        });

        // When company is deleted, hide all its jobs by setting status to rejected
        static::deleting(function ($company) {
            // Set all company jobs to rejected status so they don't appear in listings
            $company->jobs()->update(['status' => 2]); // 2 = rejected/hidden
        });

        // When company is restored, you may want to manually review and re-approve jobs
        static::restoring(function ($company) {
            // Optionally restore jobs to pending status for review
            // $company->jobs()->where('status', 2)->update(['status' => 0]); // 0 = pending
        });
    }

    public function jobs()
    {
        return $this->hasMany(Job::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getLogoUrlAttribute()
    {
        if ($this->logo) {
            if (str_starts_with($this->logo, 'http')) {
                return $this->logo;
            }
            if (str_starts_with($this->logo, 'storage/')) {
                return asset($this->logo);
            }
            return asset('storage/' . $this->logo);
        }
        return null;
    }

    public function getInitialsAttribute()
    {
        return strtoupper(substr($this->name, 0, 1));
    }
}

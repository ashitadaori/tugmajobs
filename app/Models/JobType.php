<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class JobType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($jobType) {
            $jobType->slug = $jobType->slug ?? Str::slug($jobType->name);
        });

        static::updating(function ($jobType) {
            if ($jobType->isDirty('name') && !$jobType->isDirty('slug')) {
                $jobType->slug = Str::slug($jobType->name);
            }
        });
    }

    public function jobs()
    {
        return $this->hasMany(Job::class);
    }
}

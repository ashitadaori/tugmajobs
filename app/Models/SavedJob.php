<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedJob extends Model
{
    use HasFactory;

    protected $table = 'bookmarked_jobs';

    protected $fillable = [
        'user_id',
        'job_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who saved the job
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the saved job
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * Check if a job is saved by a user
     */
    public static function isJobSavedByUser(int $jobId, int $userId): bool
    {
        return self::where('job_id', $jobId)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Get saved jobs count for a user
     */
    public static function getSavedJobsCount(int $userId): int
    {
        return self::where('user_id', $userId)->count();
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookmarkedJob extends Model
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
     * Get the user who bookmarked the job
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the bookmarked job
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * Check if a job is bookmarked by a user
     */
    public static function isJobBookmarkedByUser(int $jobId, int $userId): bool
    {
        return self::where('job_id', $jobId)
                   ->where('user_id', $userId)
                   ->exists();
    }

    /**
     * Get bookmarked jobs count for a user
     */
    public static function getBookmarkedJobsCount(int $userId): int
    {
        return self::where('user_id', $userId)->count();
    }
}

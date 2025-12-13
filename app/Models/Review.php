<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'job_id',
        'employer_id',
        'review_type',
        'rating',
        'title',
        'comment',
        'is_anonymous',
        'is_verified_hire',
        'helpful_count',
        'employer_response',
        'employer_responded_at',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'is_verified_hire' => 'boolean',
        'employer_responded_at' => 'datetime',
    ];

    /**
     * Get the jobseeker who wrote the review
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the job being reviewed
     */
    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * Get the employer/company being reviewed
     */
    public function employer()
    {
        return $this->belongsTo(User::class, 'employer_id');
    }

    /**
     * Check if user can review this job
     */
    public static function canUserReview($userId, $jobId, $reviewType)
    {
        // Check if user has applied to this job
        $hasApplied = \App\Models\JobApplication::where('user_id', $userId)
            ->where('job_id', $jobId)
            ->exists();

        if (!$hasApplied) {
            return false;
        }

        // Check if user has already reviewed
        $hasReviewed = self::where('user_id', $userId)
            ->where('job_id', $jobId)
            ->where('review_type', $reviewType)
            ->exists();

        return !$hasReviewed;
    }

    /**
     * Get average rating for a job
     */
    public static function getJobAverageRating($jobId)
    {
        return self::where('job_id', $jobId)
            ->where('review_type', 'job')
            ->avg('rating');
    }

    /**
     * Get average rating for a company
     */
    public static function getCompanyAverageRating($employerId)
    {
        return self::where('employer_id', $employerId)
            ->where('review_type', 'company')
            ->avg('rating');
    }

    /**
     * Get rating distribution for a job
     */
    public static function getJobRatingDistribution($jobId)
    {
        return self::where('job_id', $jobId)
            ->where('review_type', 'job')
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->orderBy('rating', 'desc')
            ->pluck('count', 'rating')
            ->toArray();
    }

    /**
     * Get rating distribution for a company
     */
    public static function getCompanyRatingDistribution($employerId)
    {
        return self::where('employer_id', $employerId)
            ->where('review_type', 'company')
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->orderBy('rating', 'desc')
            ->pluck('count', 'rating')
            ->toArray();
    }
}

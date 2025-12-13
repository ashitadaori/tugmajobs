<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileView extends Model
{
    use HasFactory;

    protected $fillable = [
        'jobseeker_id',
        'viewer_id',
        'viewer_type',
        'viewer_ip',
        'viewer_user_agent',
        'source',
        'job_application_id',
        'viewed_at'
    ];

    protected $casts = [
        'viewed_at' => 'datetime'
    ];

    public function jobseeker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'jobseeker_id');
    }

    public function viewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viewer_id');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class, 'job_application_id');
    }

    /**
     * Record a profile view
     */
    public static function recordView($jobseekerId, $viewerId = null, $source = null, $applicationId = null)
    {
        // Don't record if viewing own profile
        if ($viewerId && $viewerId == $jobseekerId) {
            return null;
        }

        // Check if this viewer already viewed this profile in the last hour (prevent spam)
        $recentView = self::where('jobseeker_id', $jobseekerId)
            ->where('viewer_id', $viewerId)
            ->where('viewed_at', '>', now()->subHour())
            ->first();

        if ($recentView) {
            return $recentView;
        }

        return self::create([
            'jobseeker_id' => $jobseekerId,
            'viewer_id' => $viewerId,
            'viewer_type' => $viewerId ? (User::find($viewerId)->role ?? 'guest') : 'guest',
            'viewer_ip' => request()->ip(),
            'viewer_user_agent' => request()->userAgent(),
            'source' => $source,
            'job_application_id' => $applicationId,
            'viewed_at' => now()
        ]);
    }
}

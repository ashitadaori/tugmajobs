<?php

namespace App\Repositories;

use App\Models\JobApplication;
use App\Services\CacheService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class JobApplicationRepository
{
    protected CacheService $cache;

    public function __construct(CacheService $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Get applications for a specific job
     */
    public function getByJob(int $jobId, int $perPage = 15): LengthAwarePaginator
    {
        return JobApplication::with(['user', 'job'])
            ->where('job_id', $jobId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get applications by user (jobseeker)
     */
    public function getByUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return JobApplication::with(['job.category', 'job.jobType', 'job.employer'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get applications by employer
     */
    public function getByEmployer(int $employerId, int $perPage = 15): LengthAwarePaginator
    {
        return JobApplication::with(['user', 'job'])
            ->where('employer_id', $employerId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get shortlisted applications for an employer
     */
    public function getShortlisted(int $employerId, int $perPage = 15): LengthAwarePaginator
    {
        return JobApplication::with(['user', 'job'])
            ->where('employer_id', $employerId)
            ->where('shortlisted', true)
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Find application by ID
     */
    public function findById(int $id): ?JobApplication
    {
        return JobApplication::with(['user', 'job', 'job.category', 'job.jobType'])
            ->find($id);
    }

    /**
     * Check if user has already applied to a job
     */
    public function hasApplied(int $userId, int $jobId): bool
    {
        return JobApplication::where('user_id', $userId)
            ->where('job_id', $jobId)
            ->exists();
    }

    /**
     * Create a new application
     */
    public function create(array $data): JobApplication
    {
        return JobApplication::create($data);
    }

    /**
     * Update an application
     */
    public function update(JobApplication $application, array $data): JobApplication
    {
        $application->update($data);
        return $application->fresh();
    }

    /**
     * Update application status
     */
    public function updateStatus(JobApplication $application, string $status): JobApplication
    {
        $application->update(['status' => $status]);
        return $application->fresh();
    }

    /**
     * Toggle shortlist status
     */
    public function toggleShortlist(JobApplication $application): JobApplication
    {
        $application->update(['shortlisted' => !$application->shortlisted]);
        return $application->fresh();
    }

    /**
     * Delete an application
     */
    public function delete(JobApplication $application): bool
    {
        return $application->delete();
    }

    /**
     * Get application statistics for employer
     */
    public function getEmployerStatistics(int $employerId): array
    {
        $cacheKey = "employer_app_stats_{$employerId}";

        return $this->cache->remember($cacheKey, function () use ($employerId) {
            return [
                'total' => JobApplication::where('employer_id', $employerId)->count(),
                'pending' => JobApplication::where('employer_id', $employerId)->where('status', 'pending')->count(),
                'approved' => JobApplication::where('employer_id', $employerId)->where('status', 'approved')->count(),
                'rejected' => JobApplication::where('employer_id', $employerId)->where('status', 'rejected')->count(),
                'shortlisted' => JobApplication::where('employer_id', $employerId)->where('shortlisted', true)->count(),
                'hired' => JobApplication::where('employer_id', $employerId)->where('status', 'hired')->count(),
                'this_week' => JobApplication::where('employer_id', $employerId)
                    ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                    ->count(),
            ];
        }, CacheService::TTL_SHORT);
    }

    /**
     * Get applications by status
     */
    public function getByStatus(int $employerId, string $status, int $perPage = 15): LengthAwarePaginator
    {
        return JobApplication::with(['user', 'job'])
            ->where('employer_id', $employerId)
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get applications by stage
     */
    public function getByStage(int $employerId, string $stage, int $perPage = 15): LengthAwarePaginator
    {
        return JobApplication::with(['user', 'job'])
            ->where('employer_id', $employerId)
            ->where('stage', $stage)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}

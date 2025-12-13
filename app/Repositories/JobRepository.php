<?php

namespace App\Repositories;

use App\Models\Job;
use App\Services\CacheService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class JobRepository
{
    protected CacheService $cache;

    public function __construct(CacheService $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Get all approved jobs with caching
     *
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getApprovedJobs(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        // Don't cache paginated results, only the query
        $query = Job::with(['user', 'category', 'jobType'])
            ->where('status', 1)
            ->where('status', '!=', 4);

        // Apply filters
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['job_type_id'])) {
            $query->where('job_type_id', $filters['job_type_id']);
        }

        if (!empty($filters['location'])) {
            $query->where('location', 'like', '%' . $filters['location'] . '%');
        }

        if (!empty($filters['keyword'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['keyword'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['keyword'] . '%');
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get featured jobs with caching
     *
     * @param int $limit
     * @return Collection
     */
    public function getFeaturedJobs(int $limit = 8): Collection
    {
        $cacheKey = $this->cache->jobsKey(['featured' => true, 'limit' => $limit]);

        return $this->cache->remember($cacheKey, function () use ($limit) {
            return Job::with(['user', 'category', 'jobType', 'applications'])
                ->where('status', 1)
                ->where('is_featured', true)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
        }, CacheService::TTL_MEDIUM);
    }

    /**
     * Get recent jobs with caching
     *
     * @param int $limit
     * @return Collection
     */
    public function getRecentJobs(int $limit = 10): Collection
    {
        $cacheKey = $this->cache->jobsKey(['recent' => true, 'limit' => $limit]);

        return $this->cache->remember($cacheKey, function () use ($limit) {
            return Job::with(['user', 'category', 'jobType'])
                ->where('status', 1)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
        }, CacheService::TTL_SHORT);
    }

    /**
     * Get job by ID with caching
     *
     * @param int $id
     * @return Job|null
     */
    public function findById(int $id): ?Job
    {
        $cacheKey = $this->cache->jobsKey(['id' => $id]);

        return $this->cache->remember($cacheKey, function () use ($id) {
            return Job::with(['user', 'category', 'jobType', 'applications'])
                ->find($id);
        }, CacheService::TTL_MEDIUM);
    }

    /**
     * Get jobs by category with caching
     *
     * @param int $categoryId
     * @param int $limit
     * @return Collection
     */
    public function getByCategory(int $categoryId, int $limit = 10): Collection
    {
        $cacheKey = $this->cache->jobsKey(['category' => $categoryId, 'limit' => $limit]);

        return $this->cache->remember($cacheKey, function () use ($categoryId, $limit) {
            return Job::with(['user', 'jobType'])
                ->where('category_id', $categoryId)
                ->where('status', 1)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
        }, CacheService::TTL_MEDIUM);
    }

    /**
     * Get jobs by employer
     *
     * @param int $employerId
     * @return Collection
     */
    public function getByEmployer(int $employerId): Collection
    {
        // Don't cache employer-specific data as it changes frequently
        return Job::with(['category', 'jobType', 'applications'])
            ->where('user_id', $employerId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Create a new job and clear related caches
     *
     * @param array $data
     * @return Job
     */
    public function create(array $data): Job
    {
        $job = Job::create($data);

        // Clear related caches
        $this->cache->clearJobsCaches();

        return $job;
    }

    /**
     * Update a job and clear related caches
     *
     * @param Job $job
     * @param array $data
     * @return Job
     */
    public function update(Job $job, array $data): Job
    {
        $job->update($data);

        // Clear specific job cache
        $this->cache->forget($this->cache->jobsKey(['id' => $job->id]));

        // Clear related caches
        $this->cache->clearJobsCaches();

        return $job->fresh();
    }

    /**
     * Delete a job and clear related caches
     *
     * @param Job $job
     * @return bool
     */
    public function delete(Job $job): bool
    {
        $deleted = $job->delete();

        if ($deleted) {
            // Clear specific job cache
            $this->cache->forget($this->cache->jobsKey(['id' => $job->id]));

            // Clear related caches
            $this->cache->clearJobsCaches();
        }

        return $deleted;
    }

    /**
     * Get job statistics with caching
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $cacheKey = $this->cache->statsKey('jobs');

        return $this->cache->remember($cacheKey, function () {
            return [
                'total' => Job::count(),
                'active' => Job::where('status', 1)->count(),
                'pending' => Job::where('status', 0)->count(),
                'expired' => Job::where('status', 4)->count(),
                'today' => Job::whereDate('created_at', today())->count(),
                'this_week' => Job::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'this_month' => Job::whereMonth('created_at', now()->month)->count(),
            ];
        }, CacheService::TTL_SHORT);
    }
}

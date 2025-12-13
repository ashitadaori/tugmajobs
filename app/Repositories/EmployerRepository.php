<?php

namespace App\Repositories;

use App\Models\Employer;
use App\Models\User;
use App\Services\CacheService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class EmployerRepository
{
    protected CacheService $cache;

    public function __construct(CacheService $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Get all active employers with pagination
     */
    public function getActive(int $perPage = 15): LengthAwarePaginator
    {
        return Employer::with(['user'])
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get featured employers
     */
    public function getFeatured(int $limit = 6): Collection
    {
        $cacheKey = "featured_employers_{$limit}";

        return $this->cache->remember($cacheKey, function () use ($limit) {
            return Employer::with(['user', 'jobs' => function ($query) {
                    $query->where('status', 1);
                }])
                ->where('is_featured', true)
                ->where('status', 'active')
                ->withCount(['jobs' => function ($query) {
                    $query->where('status', 1);
                }])
                ->orderBy('jobs_count', 'desc')
                ->limit($limit)
                ->get();
        }, CacheService::TTL_MEDIUM);
    }

    /**
     * Get verified employers
     */
    public function getVerified(int $perPage = 15): LengthAwarePaginator
    {
        return Employer::with(['user'])
            ->where('is_verified', true)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Find employer by user ID
     */
    public function findByUserId(int $userId): ?Employer
    {
        return Employer::with(['user'])->where('user_id', $userId)->first();
    }

    /**
     * Find employer by ID
     */
    public function findById(int $id): ?Employer
    {
        return Employer::with(['user', 'jobs'])->find($id);
    }

    /**
     * Find employer by slug
     */
    public function findBySlug(string $slug): ?Employer
    {
        return Employer::with(['user', 'jobs' => function ($query) {
                $query->where('status', 1);
            }])
            ->where('company_slug', $slug)
            ->first();
    }

    /**
     * Create or update employer profile
     */
    public function createOrUpdate(int $userId, array $data): Employer
    {
        $employer = Employer::updateOrCreate(
            ['user_id' => $userId],
            $data
        );

        // Clear cache
        $this->cache->forget("featured_employers_6");

        return $employer->fresh();
    }

    /**
     * Update employer profile
     */
    public function update(Employer $employer, array $data): Employer
    {
        $employer->update($data);
        return $employer->fresh();
    }

    /**
     * Get employers with most active jobs
     */
    public function getTopHiring(int $limit = 10): Collection
    {
        $cacheKey = "top_hiring_employers_{$limit}";

        return $this->cache->remember($cacheKey, function () use ($limit) {
            return Employer::with(['user'])
                ->where('status', 'active')
                ->withCount(['jobs' => function ($query) {
                    $query->where('status', 1);
                }])
                ->having('jobs_count', '>', 0)
                ->orderBy('jobs_count', 'desc')
                ->limit($limit)
                ->get();
        }, CacheService::TTL_MEDIUM);
    }

    /**
     * Search employers by company name
     */
    public function search(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return Employer::with(['user'])
            ->where('status', 'active')
            ->where(function ($q) use ($query) {
                $q->where('company_name', 'like', "%{$query}%")
                    ->orWhere('industry', 'like', "%{$query}%")
                    ->orWhere('city', 'like', "%{$query}%");
            })
            ->orderBy('company_name')
            ->paginate($perPage);
    }

    /**
     * Get employer statistics
     */
    public function getStatistics(): array
    {
        $cacheKey = 'employer_statistics';

        return $this->cache->remember($cacheKey, function () {
            return [
                'total' => Employer::count(),
                'active' => Employer::where('status', 'active')->count(),
                'verified' => Employer::where('is_verified', true)->count(),
                'featured' => Employer::where('is_featured', true)->count(),
                'with_jobs' => Employer::has('jobs')->count(),
                'new_this_month' => Employer::whereMonth('created_at', now()->month)->count(),
            ];
        }, CacheService::TTL_SHORT);
    }

    /**
     * Increment profile views
     */
    public function incrementViews(Employer $employer): void
    {
        $employer->increment('profile_views');
    }
}

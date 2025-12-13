<?php

namespace App\Repositories;

use App\Models\JobType;
use App\Services\CacheService;
use Illuminate\Database\Eloquent\Collection;

class JobTypeRepository
{
    protected CacheService $cache;

    public function __construct(CacheService $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Get all active job types with caching
     *
     * @return Collection
     */
    public function getAllActive(): Collection
    {
        return $this->cache->remember(
            $this->cache->jobTypesKey(),
            function () {
                return JobType::where('status', true)
                    ->withCount('jobs')
                    ->orderBy('name')
                    ->get();
            },
            CacheService::TTL_VERY_LONG
        );
    }

    /**
     * Get job type by ID with caching
     *
     * @param int $id
     * @return JobType|null
     */
    public function findById(int $id): ?JobType
    {
        $cacheKey = $this->cache->jobTypesKey() . ':' . $id;

        return $this->cache->remember($cacheKey, function () use ($id) {
            return JobType::with('jobs')->find($id);
        }, CacheService::TTL_VERY_LONG);
    }

    /**
     * Create job type and clear cache
     *
     * @param array $data
     * @return JobType
     */
    public function create(array $data): JobType
    {
        $jobType = JobType::create($data);

        $this->cache->forget($this->cache->jobTypesKey());

        return $jobType;
    }

    /**
     * Update job type and clear cache
     *
     * @param JobType $jobType
     * @param array $data
     * @return JobType
     */
    public function update(JobType $jobType, array $data): JobType
    {
        $jobType->update($data);

        $this->cache->forget($this->cache->jobTypesKey());
        $this->cache->forget($this->cache->jobTypesKey() . ':' . $jobType->id);

        return $jobType->fresh();
    }

    /**
     * Delete job type and clear cache
     *
     * @param JobType $jobType
     * @return bool
     */
    public function delete(JobType $jobType): bool
    {
        $deleted = $jobType->delete();

        if ($deleted) {
            $this->cache->forget($this->cache->jobTypesKey());
            $this->cache->forget($this->cache->jobTypesKey() . ':' . $jobType->id);
        }

        return $deleted;
    }
}

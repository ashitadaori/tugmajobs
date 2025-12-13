<?php

namespace App\Repositories;

use App\Models\Category;
use App\Services\CacheService;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository
{
    protected CacheService $cache;

    public function __construct(CacheService $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Get all active categories with caching
     *
     * @return Collection
     */
    public function getAllActive(): Collection
    {
        return $this->cache->remember(
            $this->cache->categoriesKey(),
            function () {
                return Category::where('status', true)
                    ->withCount('jobs')
                    ->orderBy('name')
                    ->get();
            },
            CacheService::TTL_LONG
        );
    }

    /**
     * Get category by ID with caching
     *
     * @param int $id
     * @return Category|null
     */
    public function findById(int $id): ?Category
    {
        $cacheKey = $this->cache->categoriesKey() . ':' . $id;

        return $this->cache->remember($cacheKey, function () use ($id) {
            return Category::with('jobs')->find($id);
        }, CacheService::TTL_LONG);
    }

    /**
     * Create category and clear cache
     *
     * @param array $data
     * @return Category
     */
    public function create(array $data): Category
    {
        $category = Category::create($data);

        $this->cache->forget($this->cache->categoriesKey());

        return $category;
    }

    /**
     * Update category and clear cache
     *
     * @param Category $category
     * @param array $data
     * @return Category
     */
    public function update(Category $category, array $data): Category
    {
        $category->update($data);

        $this->cache->forget($this->cache->categoriesKey());
        $this->cache->forget($this->cache->categoriesKey() . ':' . $category->id);

        return $category->fresh();
    }

    /**
     * Delete category and clear cache
     *
     * @param Category $category
     * @return bool
     */
    public function delete(Category $category): bool
    {
        $deleted = $category->delete();

        if ($deleted) {
            $this->cache->forget($this->cache->categoriesKey());
            $this->cache->forget($this->cache->categoriesKey() . ':' . $category->id);
        }

        return $deleted;
    }
}

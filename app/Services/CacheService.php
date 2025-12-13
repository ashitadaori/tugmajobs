<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheService
{
    /**
     * Cache time-to-live configurations (in minutes)
     */
    const TTL_SHORT = 5;      // 5 minutes - for frequently changing data
    const TTL_MEDIUM = 60;    // 1 hour - for moderately dynamic data
    const TTL_LONG = 1440;    // 24 hours - for relatively static data
    const TTL_VERY_LONG = 10080; // 7 days - for very static data

    /**
     * Cache key prefixes for organization
     */
    const PREFIX_JOBS = 'jobs';
    const PREFIX_CATEGORIES = 'categories';
    const PREFIX_JOB_TYPES = 'job_types';
    const PREFIX_USERS = 'users';
    const PREFIX_APPLICATIONS = 'applications';
    const PREFIX_STATS = 'stats';

    /**
     * Remember a value in cache with automatic key generation
     *
     * @param string $key
     * @param \Closure $callback
     * @param int $ttl Time to live in minutes
     * @return mixed
     */
    public function remember(string $key, \Closure $callback, int $ttl = self::TTL_MEDIUM)
    {
        try {
            return Cache::remember($key, now()->addMinutes($ttl), $callback);
        } catch (\Exception $e) {
            Log::error('Cache remember failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return $callback();
        }
    }

    /**
     * Get a value from cache
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return Cache::get($key, $default);
    }

    /**
     * Store a value in cache
     *
     * @param string $key
     * @param mixed $value
     * @param int $ttl Time to live in minutes
     * @return bool
     */
    public function put(string $key, $value, int $ttl = self::TTL_MEDIUM): bool
    {
        try {
            return Cache::put($key, $value, now()->addMinutes($ttl));
        } catch (\Exception $e) {
            Log::error('Cache put failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Forget a value from cache
     *
     * @param string $key
     * @return bool
     */
    public function forget(string $key): bool
    {
        return Cache::forget($key);
    }

    /**
     * Flush cache by pattern
     *
     * @param string $pattern
     * @return void
     */
    public function forgetByPattern(string $pattern): void
    {
        // For Redis driver
        if (config('cache.default') === 'redis') {
            $keys = Cache::getRedis()->keys($pattern);
            if (!empty($keys)) {
                Cache::getRedis()->del($keys);
            }
        } else {
            // For file/database drivers, flush all (less efficient)
            Cache::flush();
        }
    }

    /**
     * Clear all cache
     *
     * @return bool
     */
    public function flush(): bool
    {
        return Cache::flush();
    }

    /**
     * Generate cache key for jobs
     *
     * @param array $params
     * @return string
     */
    public function jobsKey(array $params = []): string
    {
        $key = self::PREFIX_JOBS;

        if (!empty($params)) {
            $key .= ':' . md5(json_encode($params));
        }

        return $key;
    }

    /**
     * Generate cache key for categories
     *
     * @return string
     */
    public function categoriesKey(): string
    {
        return self::PREFIX_CATEGORIES . ':all';
    }

    /**
     * Generate cache key for job types
     *
     * @return string
     */
    public function jobTypesKey(): string
    {
        return self::PREFIX_JOB_TYPES . ':all';
    }

    /**
     * Generate cache key for user
     *
     * @param int $userId
     * @return string
     */
    public function userKey(int $userId): string
    {
        return self::PREFIX_USERS . ':' . $userId;
    }

    /**
     * Generate cache key for applications
     *
     * @param int $userId
     * @return string
     */
    public function applicationsKey(int $userId): string
    {
        return self::PREFIX_APPLICATIONS . ':user:' . $userId;
    }

    /**
     * Generate cache key for statistics
     *
     * @param string $type
     * @return string
     */
    public function statsKey(string $type): string
    {
        return self::PREFIX_STATS . ':' . $type;
    }

    /**
     * Clear job-related caches
     *
     * @return void
     */
    public function clearJobsCaches(): void
    {
        $this->forgetByPattern(self::PREFIX_JOBS . ':*');
        $this->forget($this->categoriesKey());
        $this->forget($this->jobTypesKey());
    }

    /**
     * Clear user-related caches
     *
     * @param int $userId
     * @return void
     */
    public function clearUserCaches(int $userId): void
    {
        $this->forget($this->userKey($userId));
        $this->forget($this->applicationsKey($userId));
    }

    /**
     * Clear statistics caches
     *
     * @return void
     */
    public function clearStatsCaches(): void
    {
        $this->forgetByPattern(self::PREFIX_STATS . ':*');
    }

    /**
     * Get cache statistics (for monitoring)
     *
     * @return array
     */
    public function getStats(): array
    {
        $driver = config('cache.default');

        $stats = [
            'driver' => $driver,
            'enabled' => true,
        ];

        if ($driver === 'redis') {
            try {
                $redis = Cache::getRedis();
                $info = $redis->info();

                $stats['redis'] = [
                    'connected_clients' => $info['connected_clients'] ?? 0,
                    'used_memory_human' => $info['used_memory_human'] ?? '0',
                    'total_commands_processed' => $info['total_commands_processed'] ?? 0,
                    'keyspace_hits' => $info['keyspace_hits'] ?? 0,
                    'keyspace_misses' => $info['keyspace_misses'] ?? 0,
                ];

                // Calculate hit rate
                $hits = $stats['redis']['keyspace_hits'];
                $misses = $stats['redis']['keyspace_misses'];
                $total = $hits + $misses;
                $stats['redis']['hit_rate'] = $total > 0 ? round(($hits / $total) * 100, 2) : 0;
            } catch (\Exception $e) {
                $stats['redis'] = ['error' => $e->getMessage()];
            }
        }

        return $stats;
    }
}

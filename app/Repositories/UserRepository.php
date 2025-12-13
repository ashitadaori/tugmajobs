<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Jobseeker;
use App\Services\CacheService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository
{
    protected CacheService $cache;

    public function __construct(CacheService $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Find user by ID
     */
    public function findById(int $id): ?User
    {
        return User::with(['employer', 'jobseeker'])->find($id);
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Get all jobseekers with pagination
     */
    public function getJobseekers(int $perPage = 15): LengthAwarePaginator
    {
        return User::with(['jobseeker'])
            ->where('role', 'jobseeker')
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get all employers with pagination
     */
    public function getEmployers(int $perPage = 15): LengthAwarePaginator
    {
        return User::with(['employer'])
            ->where('role', 'employer')
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get all admins
     */
    public function getAdmins(): Collection
    {
        return User::where('role', 'admin')
            ->where('is_active', true)
            ->get();
    }

    /**
     * Create a new user
     */
    public function create(array $data): User
    {
        return User::create($data);
    }

    /**
     * Update user
     */
    public function update(User $user, array $data): User
    {
        $user->update($data);
        return $user->fresh();
    }

    /**
     * Delete user (soft delete)
     */
    public function delete(User $user): bool
    {
        return $user->delete();
    }

    /**
     * Search users
     */
    public function search(string $query, ?string $role = null, int $perPage = 15): LengthAwarePaginator
    {
        $users = User::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%");
        });

        if ($role) {
            $users->where('role', $role);
        }

        return $users->orderBy('name')->paginate($perPage);
    }

    /**
     * Get user statistics
     */
    public function getStatistics(): array
    {
        $cacheKey = 'user_statistics';

        return $this->cache->remember($cacheKey, function () {
            return [
                'total' => User::count(),
                'jobseekers' => User::where('role', 'jobseeker')->count(),
                'employers' => User::where('role', 'employer')->count(),
                'admins' => User::where('role', 'admin')->count(),
                'active' => User::where('is_active', true)->count(),
                'verified' => User::whereNotNull('email_verified_at')->count(),
                'kyc_verified' => User::where('kyc_status', 'verified')->count(),
                'new_this_week' => User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'new_this_month' => User::whereMonth('created_at', now()->month)->count(),
            ];
        }, CacheService::TTL_SHORT);
    }

    /**
     * Get recently registered users
     */
    public function getRecent(int $limit = 10): Collection
    {
        return User::with(['employer', 'jobseeker'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Update KYC status
     */
    public function updateKycStatus(User $user, string $status, array $data = []): User
    {
        $updateData = [
            'kyc_status' => $status,
        ];

        if ($status === 'verified') {
            $updateData['kyc_verified_at'] = now();
        }

        if (!empty($data)) {
            $updateData['kyc_data'] = $data;
        }

        $user->update($updateData);
        return $user->fresh();
    }

    /**
     * Get users pending KYC verification
     */
    public function getPendingKyc(int $perPage = 15): LengthAwarePaginator
    {
        return User::where('kyc_status', 'pending')
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\User;
use App\Models\JobApplication;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GlobalSearchController extends Controller
{
    /**
     * Perform global search across all entities
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $type = $request->get('type', 'all'); // all, users, jobs, companies, applications
        $limit = $request->get('limit', 10);

        if (empty($query) || strlen($query) < 2) {
            return response()->json([
                'results' => [],
                'message' => 'Please enter at least 2 characters'
            ]);
        }

        $results = [];

        // Search Users
        if ($type === 'all' || $type === 'users') {
            $users = User::where(function($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%")
                  ->orWhere('phone', 'LIKE', "%{$query}%");
            })
            ->limit($limit)
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'type' => 'user',
                    'title' => $user->name,
                    'subtitle' => $user->email,
                    'meta' => ucfirst($user->role),
                    'badge' => $user->kyc_status === 'verified' ? 'Verified' : null,
                    'url' => route('admin.users.show', $user->id),
                    'icon' => 'bi-person'
                ];
            });

            $results['users'] = $users;
        }

        // Search Jobs
        if ($type === 'all' || $type === 'jobs') {
            $jobs = Job::where(function($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%")
                  ->orWhere('location', 'LIKE', "%{$query}%")
                  ->orWhere('company_name', 'LIKE', "%{$query}%");
            })
            ->with(['category', 'jobType', 'employer'])
            ->limit($limit)
            ->get()
            ->map(function($job) {
                return [
                    'id' => $job->id,
                    'type' => 'job',
                    'title' => $job->title,
                    'subtitle' => $job->company_name ?? 'Unknown Company',
                    'meta' => $job->location,
                    'badge' => $job->status_name,
                    'url' => route('admin.jobs.show', $job->id),
                    'icon' => 'bi-briefcase'
                ];
            });

            $results['jobs'] = $jobs;
        }

        // Search Companies (from standalone companies table)
        if ($type === 'all' || $type === 'companies') {
            $companies = Company::where(function($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%")
                  ->orWhere('website', 'LIKE', "%{$query}%");
            })
            ->limit($limit)
            ->get()
            ->map(function($company) {
                return [
                    'id' => $company->id,
                    'type' => 'company',
                    'title' => $company->name,
                    'subtitle' => $company->website ?? 'No website',
                    'meta' => $company->industry ?? 'General',
                    'badge' => $company->is_active ? 'Active' : 'Inactive',
                    'url' => route('admin.company-management.show', $company->id),
                    'icon' => 'bi-building'
                ];
            });

            $results['companies'] = $companies;
        }

        // Search Applications
        if ($type === 'all' || $type === 'applications') {
            $applications = JobApplication::whereHas('user', function($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%");
            })
            ->orWhereHas('job', function($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%");
            })
            ->with(['user', 'job'])
            ->limit($limit)
            ->get()
            ->map(function($application) {
                return [
                    'id' => $application->id,
                    'type' => 'application',
                    'title' => $application->user->name ?? 'Unknown User',
                    'subtitle' => 'Applied for: ' . ($application->job->title ?? 'Unknown Job'),
                    'meta' => $application->created_at->diffForHumans(),
                    'badge' => ucfirst($application->status),
                    'url' => '#', // Could link to application detail if you have one
                    'icon' => 'bi-file-earmark-text'
                ];
            });

            $results['applications'] = $applications;
        }

        // Flatten results if searching all
        if ($type === 'all') {
            $allResults = collect($results)->flatten(1)->sortByDesc('id')->take($limit)->values();
            $totalCount = collect($results)->flatten(1)->count();

            return response()->json([
                'results' => $allResults,
                'counts' => [
                    'users' => $results['users']->count(),
                    'jobs' => $results['jobs']->count(),
                    'companies' => $results['companies']->count(),
                    'applications' => $results['applications']->count(),
                ],
                'total' => $totalCount,
                'query' => $query
            ]);
        }

        return response()->json([
            'results' => $results[$type] ?? [],
            'total' => ($results[$type] ?? collect())->count(),
            'query' => $query,
            'type' => $type
        ]);
    }

    /**
     * Save search preset
     */
    public function savePreset(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'filters' => 'required|array'
        ]);

        $userId = auth()->id();

        $preset = DB::table('search_presets')->insertGetId([
            'user_id' => $userId,
            'name' => $request->name,
            'filters' => json_encode($request->filters),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Search preset saved successfully',
            'preset_id' => $preset
        ]);
    }

    /**
     * Get user's saved presets
     */
    public function getPresets()
    {
        $userId = auth()->id();

        $presets = DB::table('search_presets')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($preset) {
                $preset->filters = json_decode($preset->filters, true);
                return $preset;
            });

        return response()->json($presets);
    }

    /**
     * Delete search preset
     */
    public function deletePreset($id)
    {
        $userId = auth()->id();

        $deleted = DB::table('search_presets')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->delete();

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Preset deleted successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Preset not found'
        ], 404);
    }

    /**
     * Save recent search
     */
    public function saveRecentSearch(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:255',
            'type' => 'required|string'
        ]);

        $userId = auth()->id();

        // Check if search already exists in recent
        $exists = DB::table('recent_searches')
            ->where('user_id', $userId)
            ->where('query', $request->query)
            ->where('type', $request->type)
            ->first();

        if ($exists) {
            // Update timestamp
            DB::table('recent_searches')
                ->where('id', $exists->id)
                ->update(['searched_at' => now()]);
        } else {
            // Insert new
            DB::table('recent_searches')->insert([
                'user_id' => $userId,
                'query' => $request->query,
                'type' => $request->type,
                'searched_at' => now(),
                'created_at' => now()
            ]);
        }

        // Keep only last 20 searches
        $recentIds = DB::table('recent_searches')
            ->where('user_id', $userId)
            ->orderBy('searched_at', 'desc')
            ->limit(20)
            ->pluck('id');

        DB::table('recent_searches')
            ->where('user_id', $userId)
            ->whereNotIn('id', $recentIds)
            ->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Get recent searches
     */
    public function getRecentSearches()
    {
        $userId = auth()->id();

        $searches = DB::table('recent_searches')
            ->where('user_id', $userId)
            ->orderBy('searched_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json($searches);
    }

    /**
     * Clear recent searches
     */
    public function clearRecentSearches()
    {
        $userId = auth()->id();

        DB::table('recent_searches')
            ->where('user_id', $userId)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Recent searches cleared'
        ]);
    }
}

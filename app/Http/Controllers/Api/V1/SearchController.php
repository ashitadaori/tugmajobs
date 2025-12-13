<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Employer;
use App\Models\Job;
use App\Models\JobType;
use App\Models\SavedSearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    /**
     * Autocomplete suggestions for job search
     */
    public function autocomplete(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100',
            'type' => 'nullable|in:jobs,companies,all',
        ]);

        $query = $request->q;
        $type = $request->get('type', 'all');
        $cacheKey = "autocomplete:{$type}:" . md5($query);

        $suggestions = Cache::remember($cacheKey, 300, function () use ($query, $type) {
            $results = [];

            // Job title suggestions
            if ($type === 'all' || $type === 'jobs') {
                $jobTitles = Job::where('status', 1)
                    ->where('title', 'like', "%{$query}%")
                    ->select('title')
                    ->distinct()
                    ->limit(5)
                    ->pluck('title')
                    ->map(fn($title) => ['type' => 'job', 'value' => $title]);

                $results = array_merge($results, $jobTitles->toArray());
            }

            // Company suggestions
            if ($type === 'all' || $type === 'companies') {
                $companies = Employer::where('status', 'active')
                    ->where('company_name', 'like', "%{$query}%")
                    ->select('company_name')
                    ->distinct()
                    ->limit(5)
                    ->pluck('company_name')
                    ->map(fn($name) => ['type' => 'company', 'value' => $name]);

                $results = array_merge($results, $companies->toArray());
            }

            // Category suggestions
            if ($type === 'all') {
                $categories = Category::where('status', 1)
                    ->where('name', 'like', "%{$query}%")
                    ->select('name')
                    ->limit(3)
                    ->pluck('name')
                    ->map(fn($name) => ['type' => 'category', 'value' => $name]);

                $results = array_merge($results, $categories->toArray());
            }

            return array_slice($results, 0, 10);
        });

        return response()->json([
            'success' => true,
            'data' => $suggestions,
        ]);
    }

    /**
     * Get popular/trending searches
     */
    public function trending(): JsonResponse
    {
        $cacheKey = 'trending_searches';

        $trending = Cache::remember($cacheKey, 3600, function () {
            // Get popular job titles based on views/applications
            $popularJobs = Job::where('status', 1)
                ->select('title', DB::raw('COUNT(*) as count'))
                ->groupBy('title')
                ->orderByDesc('count')
                ->limit(5)
                ->pluck('title');

            // Get popular categories
            $popularCategories = Category::where('status', 1)
                ->withCount(['jobs' => fn($q) => $q->where('status', 1)])
                ->orderByDesc('jobs_count')
                ->limit(5)
                ->pluck('name');

            return [
                'job_titles' => $popularJobs,
                'categories' => $popularCategories,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $trending,
        ]);
    }

    /**
     * Advanced job search
     */
    public function jobs(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'nullable|string|max:100',
            'category_id' => 'nullable|integer|exists:categories,id',
            'job_type_id' => 'nullable|integer|exists:job_types,id',
            'location' => 'nullable|string|max:100',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0',
            'experience_level' => 'nullable|in:entry,junior,mid,senior,executive',
            'is_remote' => 'nullable|boolean',
            'posted_within' => 'nullable|in:24h,7d,30d',
            'sort_by' => 'nullable|in:relevance,date,salary',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $query = Job::with(['category', 'jobType', 'employer'])
            ->where('status', 1);

        // Keyword search
        if ($request->filled('q')) {
            $keyword = $request->q;
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%")
                    ->orWhere('requirements', 'like', "%{$keyword}%");
            });
        }

        // Category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Job type filter
        if ($request->filled('job_type_id')) {
            $query->where('job_type_id', $request->job_type_id);
        }

        // Location filter
        if ($request->filled('location')) {
            $location = $request->location;
            $query->where(function ($q) use ($location) {
                $q->where('location', 'like', "%{$location}%")
                    ->orWhere('city', 'like', "%{$location}%")
                    ->orWhere('province', 'like', "%{$location}%");
            });
        }

        // Salary filters
        if ($request->filled('salary_min')) {
            $query->where('salary_max', '>=', $request->salary_min);
        }
        if ($request->filled('salary_max')) {
            $query->where('salary_min', '<=', $request->salary_max);
        }

        // Experience level filter
        if ($request->filled('experience_level')) {
            $query->where('experience_level', $request->experience_level);
        }

        // Remote work filter
        if ($request->filled('is_remote')) {
            $query->where('is_remote', $request->boolean('is_remote'));
        }

        // Posted within filter
        if ($request->filled('posted_within')) {
            $date = match ($request->posted_within) {
                '24h' => now()->subDay(),
                '7d' => now()->subWeek(),
                '30d' => now()->subMonth(),
                default => null,
            };
            if ($date) {
                $query->where('created_at', '>=', $date);
            }
        }

        // Sorting
        switch ($request->get('sort_by', 'date')) {
            case 'salary':
                $query->orderByDesc('salary_max');
                break;
            case 'relevance':
                // If keyword search, relevance is already handled by MySQL
                $query->orderByDesc('is_featured')->orderByDesc('created_at');
                break;
            default:
                $query->orderByDesc('created_at');
        }

        $perPage = $request->get('per_page', 15);
        $jobs = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $jobs->items(),
            'meta' => [
                'current_page' => $jobs->currentPage(),
                'last_page' => $jobs->lastPage(),
                'per_page' => $jobs->perPage(),
                'total' => $jobs->total(),
            ],
            'filters' => [
                'categories' => Category::where('status', 1)->select('id', 'name')->get(),
                'job_types' => JobType::where('status', 1)->select('id', 'name')->get(),
            ],
        ]);
    }

    /**
     * Save a search for the authenticated user
     */
    public function saveSearch(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'filters' => 'required|array',
            'email_alerts' => 'boolean',
        ]);

        $savedSearch = SavedSearch::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'filters' => $request->filters,
            'email_alerts' => $request->boolean('email_alerts', false),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Search saved successfully',
            'data' => $savedSearch,
        ], 201);
    }

    /**
     * Get user's saved searches
     */
    public function savedSearches(): JsonResponse
    {
        $searches = SavedSearch::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $searches,
        ]);
    }

    /**
     * Delete a saved search
     */
    public function deleteSavedSearch(int $id): JsonResponse
    {
        $search = SavedSearch::where('user_id', auth()->id())
            ->where('id', $id)
            ->first();

        if (!$search) {
            return response()->json([
                'success' => false,
                'message' => 'Saved search not found',
            ], 404);
        }

        $search->delete();

        return response()->json([
            'success' => true,
            'message' => 'Saved search deleted',
        ]);
    }

    /**
     * Execute a saved search
     */
    public function runSavedSearch(int $id, Request $request): JsonResponse
    {
        $search = SavedSearch::where('user_id', auth()->id())
            ->where('id', $id)
            ->first();

        if (!$search) {
            return response()->json([
                'success' => false,
                'message' => 'Saved search not found',
            ], 404);
        }

        // Merge saved filters with request
        $request->merge($search->filters);

        return $this->jobs($request);
    }
}

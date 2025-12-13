<?php

namespace App\Http\Controllers;

use App\Mail\JobNotificationEmail;
use App\Models\Category;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobType;
use App\Models\SavedJob;
use App\Models\User;
use App\Services\KMeansClusteringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Services\LocationService;
use App\Notifications\JobSavedNotification;
use App\Models\JobView;

class JobsControllerKMeans extends Controller
{
    protected $clusteringService;

    public function __construct(KMeansClusteringService $clusteringService)
    {
        $this->clusteringService = $clusteringService;
    }

    /**
     * Show jobs page with category filtering and k-means recommendations
     */
    public function index(Request $request)
    {
        // Always start with all approved jobs as base query
        $query = Job::where('status', Job::STATUS_APPROVED);
        
        // For authenticated jobseekers with preferences, filter by categories
        $userHasPreferences = false;
        $userCategories = [];
        
        if (Auth::check() && Auth::user()->role === 'jobseeker') {
            $user = Auth::user();

            // Load jobseeker profile
            $user->load('jobSeekerProfile');

            // Check if user has category preferences
            if ($this->userHasCategoryPreferences($user)) {
                $userHasPreferences = true;

                $profile = $user->jobSeekerProfile;
                $userCategories = $profile->preferred_categories;

                // Handle both JSON string and array formats
                if (is_string($userCategories)) {
                    $userCategories = json_decode($userCategories, true) ?: [];
                }

                // Filter jobs by user's preferred categories - show ALL jobs in selected categories
                if (!empty($userCategories)) {
                    $query->whereIn('category_id', $userCategories);
                }
            }
            // If no preferences, show all jobs but display category selection prompt
        }

        // Apply search filters (support both 'keyword' and 'keywords' for backward compatibility)
        $searchKeyword = $request->keyword ?: $request->keywords;
        if (!empty($searchKeyword)) {
            $query->where(function($q) use ($searchKeyword) {
                $q->where('title', 'like', '%'.$searchKeyword.'%')
                  ->orWhere('description', 'like', '%'.$searchKeyword.'%')
                  ->orWhere('requirements', 'like', '%'.$searchKeyword.'%');
            });
        }

        // Location-based search
        if (!empty($request->location)) {
            $locationText = $request->location;

            // If coordinates are provided, use combined search (radius OR text match)
            if (!empty($request->location_filter_latitude) && !empty($request->location_filter_longitude)) {
                $latitude = $request->location_filter_latitude;
                $longitude = $request->location_filter_longitude;
                $radius = $request->radius ?? 10;

                // Use a subquery approach: jobs within distance OR matching location text
                $query->where(function($q) use ($latitude, $longitude, $radius, $locationText) {
                    // Jobs with coordinates within radius
                    $q->where(function($subQ) use ($latitude, $longitude, $radius) {
                        $subQ->whereNotNull('latitude')
                             ->whereNotNull('longitude')
                             ->whereRaw("(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) <= ?",
                                 [$latitude, $longitude, $latitude, $radius]);
                    })
                    // OR jobs matching location text (for jobs without coordinates)
                    ->orWhere(function($subQ) use ($locationText) {
                        // Skip if location is just "Current Location"
                        if (strtolower($locationText) !== 'current location') {
                            $subQ->where('location', 'like', '%'.$locationText.'%')
                                 ->orWhere('address', 'like', '%'.$locationText.'%')
                                 ->orWhere('barangay', 'like', '%'.$locationText.'%')
                                 ->orWhere('city', 'like', '%'.$locationText.'%');
                        }
                    });
                });
            } else {
                // Text-only search (no coordinates)
                $query->where(function($q) use ($locationText) {
                    $q->where('location', 'like', '%'.$locationText.'%')
                      ->orWhere('address', 'like', '%'.$locationText.'%')
                      ->orWhere('barangay', 'like', '%'.$locationText.'%')
                      ->orWhere('city', 'like', '%'.$locationText.'%');
                });
            }
        }

        // Job type filter
        if (!empty($request->jobType)) {
            $query->whereHas('jobType', function($q) use ($request) {
                $q->where('name', $request->jobType);
            });
        }

        // Category filter (additional to user preferences)
        if (!empty($request->category)) {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('name', $request->category);
            });
        }

        // Apply sort order (1 = Latest/DESC, 0 = Oldest/ASC)
        $sortOrder = $request->sort == '0' ? 'ASC' : 'DESC';

        // Always get the filtered jobs first (respects user preferences and search filters)
        $jobs = $query->with(['jobType', 'category', 'employer.employerProfile'])
                     ->orderBy('created_at', $sortOrder)
                     ->paginate(10);
        
        // For authenticated jobseekers, get k-means recommendations as additional data
        $recommendedJobs = collect();
        
        if (Auth::check() && Auth::user()->role === 'jobseeker' && $userHasPreferences) {
            try {
                $recommendedJobs = $this->clusteringService->getJobRecommendations(Auth::id(), 5);
                
                // Filter recommendations to only include jobs in user's preferred categories
                if ($recommendedJobs->isNotEmpty() && !empty($userCategories)) {
                    $recommendedJobs = $recommendedJobs->whereIn('category_id', $userCategories);
                }
                
            } catch (\Exception $e) {
                \Log::warning('K-means recommendations failed: ' . $e->getMessage());
            }
        }
        $jobTypes = JobType::where('status', 1)->get();
        $categories = Category::where('status', 1)->get();

        // Determine message for users without preferences
        $categoryPrompt = null;
        if (Auth::check() && Auth::user()->role === 'jobseeker' && !$userHasPreferences) {
            $categoryPrompt = 'Set your job category preferences to get personalized recommendations!';
        }

        return view('front.modern-jobs', [
            'jobs' => $jobs,
            'jobTypes' => $jobTypes,
            'categories' => $categories,
            'recommendedJobs' => $recommendedJobs,
            'userHasPreferences' => $userHasPreferences,
            'categoryPrompt' => $categoryPrompt,
            'userCategories' => $userCategories
        ]);
    }

    /**
     * Show job detail page with related jobs using k-means clustering
     */
    public function jobDetail($id)
    {
        $job = Job::with([
            'jobType',
            'category',
            'company',
            'employer' => function($query) {
                $query->with('employerProfile');
            },
            'applications'
        ])->findOrFail($id);

        // Record job view
        JobView::recordView($job, request());

        $count = 0;
        if (Auth::check()) {
            $count = SavedJob::where([
                'user_id' => Auth::user()->id,
                'job_id' => $id
            ])->count();
        }

        // Get related jobs using k-means clustering if user is authenticated
        $relatedJobs = collect();
        if (Auth::check() && Auth::user()->role === 'jobseeker') {
            try {
                // Get user recommendations and filter for similar jobs
                $recommendations = $this->clusteringService->getJobRecommendations(Auth::id(), 10);
                $relatedJobs = $recommendations->where('id', '!=', $id)
                                             ->where('category_id', $job->category_id)
                                             ->take(3);
            } catch (\Exception $e) {
                \Log::warning('Related jobs clustering failed: ' . $e->getMessage());
            }
        }
        
        // Fallback to traditional related jobs if clustering fails or no user
        if ($relatedJobs->isEmpty()) {
            $relatedJobs = Job::where('status', 1)
                             ->where('id', '!=', $id)
                             ->where(function($query) use ($job) {
                                 $query->where('location', 'like', '%'.explode(',', $job->location)[0].'%')
                                      ->orWhere('job_type_id', $job->job_type_id)
                                      ->orWhere('category_id', $job->category_id);
                             })
                             ->with(['jobType', 'category', 'employer.employerProfile'])
                             ->take(3)
                             ->get();
        }

        return view('front.modern-job-detail', [
            'job' => $job,
            'count' => $count,
            'relatedJobs' => $relatedJobs,
            'applications' => $job->applications
        ]);
    }

    /**
     * Get personalized job recommendations for authenticated jobseeker
     */
    public function getRecommendations(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'jobseeker') {
            return response()->json([
                'status' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        $user = Auth::user();
        
        // Check if user has category preferences
        if (!$this->userHasCategoryPreferences($user)) {
            return response()->json([
                'status' => false,
                'message' => 'Please select your job category preferences first',
                'redirect' => route('account.myProfile')
            ]);
        }

        try {
            $limit = $request->input('limit', 5);
            $recommendations = $this->clusteringService->getJobRecommendations($user->id, $limit);
            
            return response()->json([
                'status' => true,
                'data' => $recommendations->map(function($job) {
                    return [
                        'id' => $job->id,
                        'title' => $job->title,
                        'company' => $job->employer->employerProfile->company_name ?? $job->employer->name,
                        'location' => $job->location,
                        'category' => $job->category->name ?? 'N/A',
                        'job_type' => $job->jobType->name ?? 'N/A',
                        'created_at' => $job->created_at->diffForHumans(),
                        'url' => route('jobDetail', $job->id)
                    ];
                })
            ]);
        } catch (\Exception $e) {
            \Log::error('Job recommendations error: ' . $e->getMessage());
            
            return response()->json([
                'status' => false,
                'message' => 'Failed to get recommendations'
            ], 500);
        }
    }

    /**
     * Dashboard endpoint for jobseekers with clustering insights
     */
    public function dashboard()
    {
        if (!Auth::check() || Auth::user()->role !== 'jobseeker') {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Check category preferences
        if (!$this->userHasCategoryPreferences($user)) {
            return redirect()->route('account.myProfile')
                ->with('error', 'Please select your preferred job categories to see personalized recommendations.');
        }

        try {
            // Get personalized recommendations
            $recommendations = $this->clusteringService->getJobRecommendations($user->id, 6);

            // Load jobseeker profile
            $user->load('jobSeekerProfile');
            $profile = $user->jobSeekerProfile;

            // Get user's preferred categories
            $userCategories = $profile->preferred_categories ?? [];

            // Handle both JSON string and array formats
            if (is_string($userCategories)) {
                $userCategories = json_decode($userCategories, true) ?: [];
            }

            $categoryNames = Category::whereIn('id', $userCategories)->pluck('name')->toArray();
            
            // Get jobs in user's categories
            $categoryJobs = Job::where('status', 1)
                              ->whereIn('category_id', $userCategories)
                              ->with(['category', 'jobType', 'employer'])
                              ->latest()
                              ->take(8)
                              ->get();
            
            // Get clustering insights
            $insights = $this->clusteringService->getLaborMarketInsights();
            
            return view('front.account.jobseeker-dashboard-kmeans', [
                'recommendations' => $recommendations,
                'categoryJobs' => $categoryJobs,
                'userCategories' => $categoryNames,
                'insights' => $insights,
                'user' => $user
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Dashboard clustering error: ' . $e->getMessage());
            
            // Fallback to regular dashboard
            return redirect()->route('account.dashboard')
                ->with('warning', 'Personalized recommendations temporarily unavailable.');
        }
    }

    /**
     * Force category selection for jobseekers
     */
    public function requireCategorySelection()
    {
        if (!Auth::check() || Auth::user()->role !== 'jobseeker') {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        if ($this->userHasCategoryPreferences($user)) {
            return redirect()->route('jobs');
        }

        $categories = Category::where('status', 1)->get();
        $jobTypes = JobType::where('status', 1)->get();

        return view('front.account.select-job-preferences', [
            'categories' => $categories,
            'jobTypes' => $jobTypes,
            'user' => $user
        ]);
    }

    /**
     * Save user's job preferences
     */
    public function saveJobPreferences(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'jobseeker') {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'categories' => 'required|array|min:1',
            'categories.*' => 'exists:categories,id',
            'job_types' => 'nullable|array',
            'job_types.*' => 'exists:job_types,id'
        ]);

        $user = Auth::user();
        $user->preferred_categories = json_encode($request->categories);
        $user->preferred_job_types = json_encode($request->job_types ?: []);
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Job preferences saved successfully',
            'redirect' => route('jobs')
        ]);
    }

    /**
     * Check if user has category preferences
     */
    private function userHasCategoryPreferences($user)
    {
        // Load jobseeker profile if not already loaded
        if (!$user->relationLoaded('jobSeekerProfile')) {
            $user->load('jobSeekerProfile');
        }

        if (!$user->jobSeekerProfile) {
            return false;
        }

        $profile = $user->jobSeekerProfile;
        $preferences = $profile->preferred_categories;

        if (empty($preferences)) {
            return false;
        }

        // Handle both JSON string and array formats
        if (is_string($preferences)) {
            $preferences = json_decode($preferences, true);
        }

        return is_array($preferences) && count($preferences) > 0;
    }

    // Keep all existing methods from the original JobsController
    // (startApplication, applyJob, saveJob, etc.) - they remain unchanged
    
    /**
     * Apply for a job (existing method - unchanged)
     */
    public function applyJob($id, Request $request)
    {
        // ... existing implementation from original controller ...
        // This method remains the same as in the original JobsController
    }

    /**
     * Save a job (existing method - unchanged)
     */
    public function saveJob($id)
    {
        // ... existing implementation from original controller ...
        // This method remains the same as in the original JobsController
    }

    /**
     * Store a new job posting (existing method - unchanged)
     */
    public function store(Request $request)
    {
        // ... existing implementation from original controller ...
        // This method remains the same as in the original JobsController
    }
}

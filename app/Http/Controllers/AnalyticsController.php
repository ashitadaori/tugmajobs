<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobType;
use App\Models\JobView;
use App\Models\User;
use App\Services\KMeansClusteringService;
use App\Services\AzureMLClusteringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    protected $clusteringService;
    protected $azureMLService;

    public function __construct(
        KMeansClusteringService $clusteringService,
        AzureMLClusteringService $azureMLService
    ) {
        $this->clusteringService = $clusteringService;
        $this->azureMLService = $azureMLService;
        $this->middleware('auth');
    }
    
    /**
     * Display the labor market insights dashboard
     * 
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        // Get labor market insights
        $insights = $this->clusteringService->getLaborMarketInsights();
        
        // Get counts for dashboard
        $totalJobs = Job::where('status', 1)->count();
        $totalUsers = User::where('role', 'user')->count();
        $totalApplications = JobApplication::count();
        $totalCategories = Category::where('status', 1)->count();
        
        return view('admin.analytics.dashboard', [
            'insights' => $insights,
            'totalJobs' => $totalJobs,
            'totalUsers' => $totalUsers,
            'totalApplications' => $totalApplications,
            'totalCategories' => $totalCategories
        ]);
    }
    
    /**
     * Display job recommendations for the current user
     * 
     * @return \Illuminate\View\View
     */
    public function jobRecommendations()
    {
        $userId = Auth::id();
        $recommendedJobs = $this->clusteringService->getJobRecommendations($userId, 10);
        
        return view('front.account.job.recommendations', [
            'recommendedJobs' => $recommendedJobs
        ]);
    }
    
    /**
     * Display candidate recommendations for a job
     * 
     * @param int $jobId
     * @return \Illuminate\View\View
     */
    public function candidateRecommendations($jobId)
    {
        $job = Job::findOrFail($jobId);
        
        // Check if the current user owns this job
        if ($job->user_id != Auth::id() && Auth::user()->role != 'admin') {
            return redirect()->route('account.employer.jobs.index')->with('error', 'You do not have permission to view this page.');
        }
        
        $recommendedUsers = $this->clusteringService->getUserRecommendations($jobId, 10);
        
        return view('front.account.job.candidate-recommendations', [
            'job' => $job,
            'recommendedUsers' => $recommendedUsers
        ]);
    }
    
    /**
     * Display job clusters visualization
     * 
     * @return \Illuminate\View\View
     */
    public function jobClusters()
    {
        $clusters = $this->clusteringService->runJobClustering();
        
        return view('admin.analytics.job-clusters', [
            'clusters' => $clusters
        ]);
    }
    
    /**
     * Display user clusters visualization
     * 
     * @return \Illuminate\View\View
     */
    public function userClusters()
    {
        $clusters = $this->clusteringService->runUserClustering();
        
        return view('admin.analytics.user-clusters', [
            'clusters' => $clusters
        ]);
    }

    public function index()
    {
        $employer = Auth::user()->employerProfile;
        $range = request('range', 'month'); // Default to month if not specified
        
        // Get date range based on selection
        $startDate = $this->getStartDate($range);
        
        // Get job metrics
        $jobMetrics = $employer->getJobMetrics($range);
        
        // Get application trends
        $applicationTrends = $this->getApplicationTrends($employer, $startDate);
        
        // Get top performing jobs
        $topJobs = $this->getTopPerformingJobs($employer);

        return view('front.account.employer.analytics', compact(
            'jobMetrics',
            'applicationTrends',
            'topJobs'
        ));
    }

    public function overview()
    {
        $employer = Auth::user()->employerProfile;
        $range = request('range', 'month');
        
        $startDate = $this->getStartDate($range);
        
        // Get job metrics
        $jobMetrics = $employer->getJobMetrics($range);
        
        // Get application trends
        $applicationTrends = $this->getApplicationTrends($employer, $startDate);
        
        // Get top performing jobs
        $topJobs = $this->getTopPerformingJobs($employer);
        
        // Get recent activity
        $recentActivity = $this->getRecentActivity($employer);

        return view('front.account.employer.analytics.overview', compact(
            'jobMetrics',
            'applicationTrends',
            'topJobs',
            'recentActivity'
        ));
    }

    private function getStartDate($range)
    {
        return match($range) {
            'week' => now()->subWeek(),
            'year' => now()->subYear(),
            default => now()->subMonth(),
        };
    }

    private function getApplicationTrends($employer, $startDate)
    {
        return JobApplication::where('employer_id', $employer->user_id)
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date)->format('M d'),
                    'count' => $item->count
                ];
            });
    }

    private function getTopPerformingJobs($employer, $limit = 5)
    {
        return Job::where('employer_id', $employer->user_id)
            ->withCount(['applications', 'views'])
            ->with(['category'])
            ->orderByDesc('applications_count')
            ->orderByDesc('views_count')
            ->limit($limit)
            ->get();
    }

    private function getRecentActivity($employer)
    {
        $activities = collect();

        // Get recent applications
        $recentApplications = JobApplication::where('employer_id', $employer->user_id)
            ->with(['job', 'user'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($application) {
                return [
                    'type' => 'application',
                    'description' => "{$application->user->name} applied for {$application->job->title}",
                    'created_at' => $application->created_at
                ];
            });
        $activities = $activities->concat($recentApplications);

        // Get recent job views
        $recentViews = JobView::whereIn('job_id', $employer->jobs->pluck('id'))
            ->with(['job'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($view) {
                return [
                    'type' => 'view',
                    'description' => "Someone viewed {$view->job->title}",
                    'created_at' => $view->created_at
                ];
            });
        $activities = $activities->concat($recentViews);

        // Sort by created_at and take most recent 5
        return $activities->sortByDesc('created_at')->take(5);
    }

    public function updateRange(Request $request)
    {
        $range = $request->input('range', 'month');
        $employer = Auth::user()->employerProfile;
        
        $startDate = $this->getStartDate($range);
        
        // Get updated metrics
        $jobMetrics = $employer->getJobMetrics($range);
        $applicationTrends = $this->getApplicationTrends($employer, $startDate);
        
        return response()->json([
            'jobMetrics' => $jobMetrics,
            'applicationTrends' => $applicationTrends
        ]);
    }

    /**
     * Display analytics for job seekers
     * 
     * @return \Illuminate\View\View
     */
    public function jobSeekerAnalytics()
    {
        $user = Auth::user();
        
        // Get application statistics
        $totalApplications = JobApplication::where('user_id', $user->id)->count();
        $pendingApplications = JobApplication::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();
        $acceptedApplications = JobApplication::where('user_id', $user->id)
            ->where('status', 'accepted')
            ->count();
        $rejectedApplications = JobApplication::where('user_id', $user->id)
            ->where('status', 'rejected')
            ->count();
        
        // Get profile view statistics
        $totalProfileViews = \App\Models\ProfileView::where('jobseeker_id', $user->id)->count();
        $profileViewsThisWeek = \App\Models\ProfileView::where('jobseeker_id', $user->id)
            ->where('viewed_at', '>=', now()->subWeek())
            ->count();
        $profileViewsThisMonth = \App\Models\ProfileView::where('jobseeker_id', $user->id)
            ->where('viewed_at', '>=', now()->subMonth())
            ->count();
        
        // Get recent profile viewers
        $recentProfileViewers = \App\Models\ProfileView::where('jobseeker_id', $user->id)
            ->with(['viewer', 'viewer.employerProfile'])
            ->latest('viewed_at')
            ->limit(10)
            ->get();
        
        // Get application trends (last 30 days) - with all dates filled
        $startDate = now()->subDays(30);
        $endDate = now();
        
        // Get actual application data
        $applicationData = JobApplication::where('user_id', $user->id)
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');
        
        // Fill in all dates with zero for missing days
        $applicationTrends = collect();
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $count = $applicationData->has($dateKey) ? $applicationData[$dateKey]->count : 0;
            
            $applicationTrends->push([
                'date' => $currentDate->format('M d'),
                'count' => $count
            ]);
            
            $currentDate->addDay();
        }
        
        // Get recent applications
        $recentApplications = JobApplication::where('user_id', $user->id)
            ->with(['job', 'job.category'])
            ->latest()
            ->limit(5)
            ->get();
        
        // Get job categories user has applied to
        $categoryStats = JobApplication::where('user_id', $user->id)
            ->join('jobs', 'job_applications.job_id', '=', 'jobs.id')
            ->join('categories', 'jobs.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('COUNT(*) as count'))
            ->groupBy('categories.name')
            ->orderByDesc('count')
            ->limit(5)
            ->get();
        
        // Get saved jobs count
        $savedJobsCount = $user->savedJobs()->count();
        
        return view('front.account.analytics', compact(
            'totalApplications',
            'pendingApplications',
            'acceptedApplications',
            'rejectedApplications',
            'applicationTrends',
            'recentApplications',
            'categoryStats',
            'savedJobsCount',
            'totalProfileViews',
            'profileViewsThisWeek',
            'profileViewsThisMonth',
            'recentProfileViewers'
        ));
    }

    /**
     * Get Azure ML clustering results
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function azureMLClustering(Request $request)
    {
        $type = $request->get('type', 'job');
        $k = $request->get('k');

        try {
            if ($type === 'job') {
                $result = $this->azureMLService->runJobClustering($k);
            } else {
                $result = $this->azureMLService->runUserClustering($k);
            }

            return response()->json([
                'success' => true,
                'data' => $result,
                'source' => $result['source'] ?? 'unknown'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Clustering failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get job recommendations using Azure ML
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function azureMLJobRecommendations(Request $request)
    {
        $userId = $request->get('user_id', Auth::id());
        $limit = $request->get('limit', 10);

        try {
            $recommendations = $this->azureMLService->getJobRecommendations($userId, $limit);

            return response()->json([
                'success' => true,
                'data' => $recommendations->map(function($job) {
                    return [
                        'id' => $job->id,
                        'title' => $job->title,
                        'company' => $job->employer->employerProfile->company_name ?? 'N/A',
                        'location' => $job->location,
                        'salary_range' => $job->salary_range,
                        'category' => $job->category->name ?? 'N/A',
                        'job_type' => $job->jobType->name ?? 'N/A',
                        'cluster_score' => $job->cluster_score ?? null,
                        'created_at' => $job->created_at->diffForHumans()
                    ];
                }),
                'count' => $recommendations->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get recommendations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get candidate recommendations using Azure ML
     *
     * @param Request $request
     * @param int $jobId
     * @return \Illuminate\Http\JsonResponse
     */
    public function azureMLCandidateRecommendations(Request $request, $jobId)
    {
        $limit = $request->get('limit', 10);

        // Check ownership
        $job = Job::find($jobId);
        if (!$job || ($job->user_id != Auth::id() && Auth::user()->role != 'admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        try {
            $recommendations = $this->azureMLService->getUserRecommendations($jobId, $limit);

            return response()->json([
                'success' => true,
                'data' => $recommendations->map(function($user) {
                    $profile = $user->jobSeekerProfile;
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'experience_years' => $profile->total_experience_years ?? 0,
                        'skills' => $profile->skills ?? [],
                        'match_score' => $user->match_score ?? null
                    ];
                }),
                'count' => $recommendations->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get candidates: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Find optimal K for clustering using Azure ML
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function azureMLOptimalK(Request $request)
    {
        $type = $request->get('type', 'job');
        $maxK = $request->get('max_k', 10);

        try {
            $result = $this->azureMLService->findOptimalK($type, $maxK);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to find optimal K: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cluster analysis with metrics from Azure ML
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function azureMLClusterAnalysis(Request $request)
    {
        $type = $request->get('type', 'job');
        $k = $request->get('k');

        try {
            $result = $this->azureMLService->getClusterAnalysis($type, $k);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cluster analysis failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get labor market insights from Azure ML
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function azureMLMarketInsights()
    {
        try {
            $insights = $this->azureMLService->getLaborMarketInsights();

            return response()->json([
                'success' => true,
                'data' => $insights
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get market insights: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check Azure ML endpoint health
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function azureMLHealth()
    {
        $health = $this->azureMLService->healthCheck();

        return response()->json([
            'success' => $health['accessible'],
            'configured' => $health['configured'],
            'message' => $health['message']
        ]);
    }

    /**
     * Clear Azure ML cache
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function azureMLClearCache()
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Admin access required'
            ], 403);
        }

        $this->azureMLService->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Azure ML cache cleared'
        ]);
    }
}
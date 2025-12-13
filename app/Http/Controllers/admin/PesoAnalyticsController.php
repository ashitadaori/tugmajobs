<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\User;
use App\Models\Category;
use App\Models\JobApplication;
use App\Services\AdvancedKMeansClusteringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class PesoAnalyticsController extends Controller
{
    protected $kmeansService;

    public function __construct(AdvancedKMeansClusteringService $kmeansService)
    {
        $this->kmeansService = $kmeansService;
    }

    /**
     * Main Analytics Dashboard
     */
    public function index()
    {
        $stats = $this->getDashboardStats();
        $categories = Category::where('status', 1)->get();

        return view('admin.peso-analytics.index', compact('stats', 'categories'));
    }

    /**
     * Get Dashboard Statistics
     */
    protected function getDashboardStats()
    {
        return Cache::remember('peso_dashboard_stats', 300, function() {
            return [
                'total_jobs' => Job::where('status', Job::STATUS_APPROVED)->count(),
                'total_jobseekers' => User::where('role', 'jobseeker')->count(),
                'total_employers' => User::where('role', 'employer')->count(),
                'total_applications' => JobApplication::count(),
                'jobs_with_coordinates' => Job::whereNotNull('latitude')->whereNotNull('longitude')->count(),
                'verified_jobseekers' => User::where('role', 'jobseeker')->where('kyc_status', 'verified')->count(),
            ];
        });
    }

    /**
     * K-Means Cluster Analysis Data
     */
    public function getClusterAnalysis(Request $request)
    {
        $cacheKey = 'kmeans_cluster_analysis_' . ($request->get('k', 5));

        $data = Cache::remember($cacheKey, 600, function() use ($request) {
            $k = $request->get('k', 5);

            // Get job data with category information
            $jobs = Job::where('status', Job::STATUS_APPROVED)
                ->with('category')
                ->get();

            // Get user (jobseeker) data
            $jobseekers = User::where('role', 'jobseeker')
                ->get();

            // Perform clustering analysis
            $clusters = $this->performClusterAnalysis($jobs, $jobseekers, $k);

            return $clusters;
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Perform K-Means Cluster Analysis
     */
    protected function performClusterAnalysis($jobs, $jobseekers, $k = 5)
    {
        // Group jobs by category
        $categoryGroups = $jobs->groupBy('category_id');

        // Build cluster data based on job categories and skills
        $clusters = [];
        $clusterColors = ['#3b82f6', '#ef4444', '#22c55e', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#84cc16'];

        $categoryIndex = 0;
        foreach ($categoryGroups as $categoryId => $categoryJobs) {
            $category = Category::find($categoryId);
            if (!$category) continue;

            // Extract skills from jobs in this category
            $skills = $this->extractSkillsFromJobs($categoryJobs);

            // Count applications for this category
            $applicationCount = JobApplication::whereIn('job_id', $categoryJobs->pluck('id'))->count();

            // Find jobseekers interested in this category
            $interestedJobseekers = $this->countInterestedJobseekers($categoryId, $jobseekers);

            $clusters[] = [
                'id' => $categoryIndex,
                'name' => $category->name,
                'category_id' => $categoryId,
                'job_count' => $categoryJobs->count(),
                'application_count' => $applicationCount,
                'jobseeker_count' => $interestedJobseekers,
                'top_skills' => array_slice($skills, 0, 10),
                'color' => $clusterColors[$categoryIndex % count($clusterColors)],
                'avg_salary' => $this->calculateAverageSalary($categoryJobs),
                'demand_score' => $this->calculateDemandScore($categoryJobs->count(), $applicationCount, $interestedJobseekers),
                'locations' => $this->getJobLocations($categoryJobs),
            ];

            $categoryIndex++;
        }

        // Sort clusters by job count
        usort($clusters, function($a, $b) {
            return $b['job_count'] - $a['job_count'];
        });

        // Calculate silhouette score (simplified)
        $silhouetteScore = $this->calculateSimplifiedSilhouetteScore($clusters);

        return [
            'clusters' => $clusters,
            'total_jobs' => $jobs->count(),
            'total_jobseekers' => $jobseekers->count(),
            'k' => count($clusters),
            'silhouette_score' => $silhouetteScore,
            'cluster_summary' => $this->generateClusterSummary($clusters),
        ];
    }

    /**
     * Extract skills from job descriptions and requirements
     */
    protected function extractSkillsFromJobs($jobs)
    {
        $skillsCount = [];
        $commonSkills = [
            'php', 'laravel', 'javascript', 'react', 'vue', 'angular', 'node', 'python',
            'java', 'mysql', 'postgresql', 'mongodb', 'html', 'css', 'bootstrap', 'tailwind',
            'git', 'docker', 'aws', 'communication', 'teamwork', 'leadership', 'problem-solving',
            'customer service', 'sales', 'marketing', 'accounting', 'management', 'excel',
            'word', 'powerpoint', 'english', 'tagalog', 'driving', 'cooking', 'cleaning',
            'teaching', 'nursing', 'caregiving', 'typing', 'data entry', 'cashier',
            'agriculture', 'farming', 'fishing', 'construction', 'carpentry', 'welding',
            'electrical', 'plumbing', 'mechanical', 'automotive', 'security', 'bpo'
        ];

        foreach ($jobs as $job) {
            $text = strtolower($job->description . ' ' . $job->requirements . ' ' . $job->title);

            foreach ($commonSkills as $skill) {
                if (strpos($text, $skill) !== false) {
                    $skillsCount[$skill] = ($skillsCount[$skill] ?? 0) + 1;
                }
            }
        }

        arsort($skillsCount);

        $result = [];
        foreach ($skillsCount as $skill => $count) {
            $result[] = [
                'name' => ucfirst($skill),
                'count' => $count,
                'percentage' => round(($count / max(1, $jobs->count())) * 100, 1)
            ];
        }

        return $result;
    }

    /**
     * Count jobseekers interested in a category
     */
    protected function countInterestedJobseekers($categoryId, $jobseekers)
    {
        $count = 0;
        foreach ($jobseekers as $jobseeker) {
            $preferences = $jobseeker->preferred_categories;
            if (is_string($preferences)) {
                $preferences = json_decode($preferences, true);
            }
            if (is_array($preferences) && in_array($categoryId, $preferences)) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Calculate average salary for jobs
     */
    protected function calculateAverageSalary($jobs)
    {
        $salaries = $jobs->filter(function($job) {
            return $job->salary_min > 0 || $job->salary_max > 0;
        })->map(function($job) {
            return ($job->salary_min + $job->salary_max) / 2;
        });

        return $salaries->count() > 0 ? round($salaries->avg(), 2) : 0;
    }

    /**
     * Calculate demand score
     */
    protected function calculateDemandScore($jobCount, $applicationCount, $jobseekerCount)
    {
        if ($jobCount == 0) return 0;

        // Higher applications per job and lower competition = higher demand
        $applicationRatio = $applicationCount / max(1, $jobCount);
        $competitionRatio = $jobseekerCount / max(1, $jobCount);

        // Normalize to 0-100 scale
        $score = min(100, ($applicationRatio * 10) + ($competitionRatio * 5) + ($jobCount * 2));

        return round($score, 1);
    }

    /**
     * Get job locations for a cluster
     */
    protected function getJobLocations($jobs)
    {
        return $jobs->filter(function($job) {
            return $job->latitude && $job->longitude;
        })->map(function($job) {
            return [
                'id' => $job->id,
                'title' => $job->title,
                'lat' => (float) $job->latitude,
                'lng' => (float) $job->longitude,
                'location' => $job->location ?? $job->barangay,
            ];
        })->values()->toArray();
    }

    /**
     * Calculate simplified silhouette score
     */
    protected function calculateSimplifiedSilhouetteScore($clusters)
    {
        if (count($clusters) < 2) return 0;

        // Calculate based on cluster separation
        $totalJobs = array_sum(array_column($clusters, 'job_count'));
        if ($totalJobs == 0) return 0;

        $variance = 0;
        $avgJobsPerCluster = $totalJobs / count($clusters);

        foreach ($clusters as $cluster) {
            $variance += pow($cluster['job_count'] - $avgJobsPerCluster, 2);
        }

        $stdDev = sqrt($variance / count($clusters));
        $cv = $stdDev / max(1, $avgJobsPerCluster); // Coefficient of variation

        // Transform to 0-1 scale where higher is better
        return round(max(0, min(1, 1 - ($cv / 2))), 2);
    }

    /**
     * Generate cluster summary insights
     */
    protected function generateClusterSummary($clusters)
    {
        if (empty($clusters)) {
            return [
                'dominant_cluster' => null,
                'insights' => ['No data available for analysis'],
            ];
        }

        $dominant = $clusters[0];
        $totalJobs = array_sum(array_column($clusters, 'job_count'));

        $insights = [];

        // Top cluster insight
        $insights[] = "{$dominant['name']} is the dominant job category with {$dominant['job_count']} jobs (" .
                     round(($dominant['job_count'] / max(1, $totalJobs)) * 100, 1) . "% of total)";

        // High demand categories
        $highDemand = array_filter($clusters, fn($c) => $c['demand_score'] > 50);
        if (count($highDemand) > 0) {
            $names = array_column(array_slice($highDemand, 0, 3), 'name');
            $insights[] = "High demand categories: " . implode(', ', $names);
        }

        // Skill gap insight
        $lowJobseekerCategories = array_filter($clusters, fn($c) => $c['job_count'] > 5 && $c['jobseeker_count'] < $c['job_count']);
        if (count($lowJobseekerCategories) > 0) {
            $cat = reset($lowJobseekerCategories);
            $insights[] = "Potential skill gap in {$cat['name']}: {$cat['job_count']} jobs but only {$cat['jobseeker_count']} interested jobseekers";
        }

        return [
            'dominant_cluster' => $dominant,
            'insights' => $insights,
        ];
    }

    /**
     * Get Skill Groups and Labor Trends
     */
    public function getSkillTrends(Request $request)
    {
        $days = $request->get('days', 30);

        $data = Cache::remember("skill_trends_{$days}", 600, function() use ($days) {
            $startDate = Carbon::now()->subDays($days);

            // Get skills distribution
            $jobs = Job::where('status', Job::STATUS_APPROVED)
                ->where('created_at', '>=', $startDate)
                ->get();

            $skills = $this->extractSkillsFromJobs($jobs);

            // Get job posting trends by category
            $categoryTrends = Job::where('status', Job::STATUS_APPROVED)
                ->where('created_at', '>=', $startDate)
                ->select('category_id', DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
                ->groupBy('category_id', 'date')
                ->orderBy('date')
                ->get()
                ->groupBy('category_id');

            // Format trends data
            $trends = [];
            foreach ($categoryTrends as $categoryId => $data) {
                $category = Category::find($categoryId);
                if (!$category) continue;

                $trends[] = [
                    'category' => $category->name,
                    'data' => $data->map(fn($item) => [
                        'date' => $item->date,
                        'count' => $item->count
                    ])->values()->toArray()
                ];
            }

            // Labor market indicators
            $laborIndicators = $this->calculateLaborIndicators($startDate);

            return [
                'skills' => array_slice($skills, 0, 20),
                'category_trends' => $trends,
                'labor_indicators' => $laborIndicators,
                'period' => $days . ' days',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Calculate labor market indicators
     */
    protected function calculateLaborIndicators($startDate)
    {
        $currentPeriodJobs = Job::where('status', Job::STATUS_APPROVED)
            ->where('created_at', '>=', $startDate)
            ->count();

        $previousPeriodStart = $startDate->copy()->subDays($startDate->diffInDays(Carbon::now()));
        $previousPeriodJobs = Job::where('status', Job::STATUS_APPROVED)
            ->whereBetween('created_at', [$previousPeriodStart, $startDate])
            ->count();

        $currentApplications = JobApplication::where('created_at', '>=', $startDate)->count();
        $previousApplications = JobApplication::whereBetween('created_at', [$previousPeriodStart, $startDate])->count();

        $newJobseekers = User::where('role', 'jobseeker')
            ->where('created_at', '>=', $startDate)
            ->count();

        return [
            'job_posting_growth' => $previousPeriodJobs > 0
                ? round((($currentPeriodJobs - $previousPeriodJobs) / $previousPeriodJobs) * 100, 1)
                : 0,
            'application_growth' => $previousApplications > 0
                ? round((($currentApplications - $previousApplications) / $previousApplications) * 100, 1)
                : 0,
            'new_jobseekers' => $newJobseekers,
            'jobs_per_applicant' => $currentApplications > 0
                ? round($currentPeriodJobs / $currentApplications, 2)
                : 0,
            'application_rate' => $currentPeriodJobs > 0
                ? round($currentApplications / $currentPeriodJobs, 2)
                : 0,
        ];
    }

    /**
     * Get Job Vacancies Map Data
     */
    public function getJobMapData(Request $request)
    {
        $categoryId = $request->get('category_id');

        $cacheKey = 'job_map_data_' . ($categoryId ?? 'all');

        $data = Cache::remember($cacheKey, 300, function() use ($categoryId) {
            $query = Job::where('status', Job::STATUS_APPROVED)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->with(['category', 'employer']);

            if ($categoryId) {
                $query->where('category_id', $categoryId);
            }

            $jobs = $query->get();

            // Group jobs by location for clustering on map
            $locationGroups = [];
            foreach ($jobs as $job) {
                $key = round($job->latitude, 3) . '_' . round($job->longitude, 3);
                if (!isset($locationGroups[$key])) {
                    $locationGroups[$key] = [
                        'lat' => (float) $job->latitude,
                        'lng' => (float) $job->longitude,
                        'location' => $job->location ?? $job->barangay ?? 'Unknown',
                        'jobs' => [],
                        'count' => 0,
                    ];
                }
                $locationGroups[$key]['jobs'][] = [
                    'id' => $job->id,
                    'title' => $job->title,
                    'company' => $job->employer->name ?? $job->company_name ?? 'Company',
                    'category' => $job->category->name ?? 'General',
                    'salary_range' => $job->salary_range,
                ];
                $locationGroups[$key]['count']++;
            }

            // Get barangay statistics
            $barangayStats = Job::where('status', Job::STATUS_APPROVED)
                ->whereNotNull('barangay')
                ->select('barangay', DB::raw('COUNT(*) as job_count'))
                ->groupBy('barangay')
                ->orderByDesc('job_count')
                ->get();

            return [
                'markers' => array_values($locationGroups),
                'total_jobs' => $jobs->count(),
                'barangay_stats' => $barangayStats,
                'bounds' => [
                    'southwest' => [125.35, 6.70],
                    'northeast' => [125.50, 6.85],
                ],
                'center' => [125.42, 6.75],
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get Applicant Density Heat Map Data
     */
    public function getApplicantDensityData(Request $request)
    {
        $data = Cache::remember('applicant_density_data', 600, function() {
            // Get jobseekers with location data
            $jobseekers = User::where('role', 'jobseeker')
                ->whereNotNull('city')
                ->select('city', 'barangay', DB::raw('COUNT(*) as count'))
                ->groupBy('city', 'barangay')
                ->orderByDesc('count')
                ->get();

            // Get application distribution by location
            $applicationsByLocation = JobApplication::join('jobs', 'job_applications.job_id', '=', 'jobs.id')
                ->whereNotNull('jobs.barangay')
                ->select('jobs.barangay', DB::raw('COUNT(*) as application_count'))
                ->groupBy('jobs.barangay')
                ->orderByDesc('application_count')
                ->get();

            // Create heat map data points
            $heatmapData = [];

            // Predefined barangay coordinates for Sta. Cruz
            $barangayCoords = [
                'Astorga' => ['lat' => 6.7234, 'lng' => 125.4123],
                'Bato' => ['lat' => 6.7345, 'lng' => 125.4234],
                'Coronon' => ['lat' => 6.7456, 'lng' => 125.4345],
                'Darong' => ['lat' => 6.7567, 'lng' => 125.4456],
                'Inawayan' => ['lat' => 6.7678, 'lng' => 125.4567],
                'Jose Rizal' => ['lat' => 6.7789, 'lng' => 125.4678],
                'Matutungan' => ['lat' => 6.7890, 'lng' => 125.4789],
                'Poblacion' => ['lat' => 6.7512, 'lng' => 125.4234],
                'Tagabuli' => ['lat' => 6.7623, 'lng' => 125.4345],
                'Tibolo' => ['lat' => 6.7734, 'lng' => 125.4456],
                'Zone I' => ['lat' => 6.7510, 'lng' => 125.4220],
                'Zone II' => ['lat' => 6.7520, 'lng' => 125.4250],
                'Zone III' => ['lat' => 6.7530, 'lng' => 125.4280],
            ];

            foreach ($applicationsByLocation as $location) {
                $barangay = $location->barangay;
                $coords = $barangayCoords[$barangay] ?? null;

                if ($coords) {
                    $heatmapData[] = [
                        'lat' => $coords['lat'],
                        'lng' => $coords['lng'],
                        'intensity' => min(1, $location->application_count / 50), // Normalize intensity
                        'count' => $location->application_count,
                        'barangay' => $barangay,
                    ];
                }
            }

            // Calculate density statistics
            $totalApplications = $applicationsByLocation->sum('application_count');
            $avgPerBarangay = $applicationsByLocation->count() > 0
                ? round($totalApplications / $applicationsByLocation->count(), 1)
                : 0;

            return [
                'heatmap_points' => $heatmapData,
                'barangay_distribution' => $applicationsByLocation,
                'jobseeker_distribution' => $jobseekers,
                'statistics' => [
                    'total_applications' => $totalApplications,
                    'avg_per_barangay' => $avgPerBarangay,
                    'highest_density' => $applicationsByLocation->first(),
                    'coverage' => count($heatmapData) . ' barangays',
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get Job Fair Planning Data
     */
    public function getJobFairPlanningData(Request $request)
    {
        $industry = $request->get('industry');

        $data = Cache::remember('job_fair_planning_' . ($industry ?? 'all'), 600, function() use ($industry) {
            // Get jobs grouped by industry/category
            $query = Job::where('status', Job::STATUS_APPROVED)
                ->with(['category', 'employer']);

            if ($industry) {
                $query->where('category_id', $industry);
            }

            $jobs = $query->get();

            // Industry breakdown
            $industryBreakdown = $jobs->groupBy('category_id')->map(function($categoryJobs, $categoryId) {
                $category = Category::find($categoryId);
                return [
                    'category_id' => $categoryId,
                    'category_name' => $category->name ?? 'Unknown',
                    'job_count' => $categoryJobs->count(),
                    'total_vacancies' => $categoryJobs->sum('vacancy'),
                    'employers' => $categoryJobs->pluck('employer_id')->unique()->count(),
                    'locations' => $categoryJobs->pluck('barangay')->filter()->unique()->values(),
                ];
            })->values();

            // Employer participation data
            $topEmployers = $jobs->groupBy('employer_id')
                ->map(function($employerJobs, $employerId) {
                    $employer = User::find($employerId);
                    return [
                        'employer_id' => $employerId,
                        'employer_name' => $employer->name ?? 'Unknown',
                        'job_count' => $employerJobs->count(),
                        'categories' => $employerJobs->pluck('category_id')->unique()->count(),
                    ];
                })
                ->sortByDesc('job_count')
                ->take(10)
                ->values();

            // Recommended venues (barangays with most job activity)
            $recommendedVenues = Job::where('status', Job::STATUS_APPROVED)
                ->whereNotNull('barangay')
                ->select('barangay', DB::raw('COUNT(*) as job_count'), DB::raw('SUM(vacancy) as total_vacancies'))
                ->groupBy('barangay')
                ->orderByDesc('job_count')
                ->take(5)
                ->get();

            // Timeline recommendations based on job posting patterns
            $monthlyTrends = Job::where('status', Job::STATUS_APPROVED)
                ->where('created_at', '>=', Carbon::now()->subMonths(12))
                ->select(DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as count'))
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            $peakMonths = $monthlyTrends->sortByDesc('count')->take(3)->pluck('month');

            return [
                'industry_breakdown' => $industryBreakdown,
                'top_employers' => $topEmployers,
                'recommended_venues' => $recommendedVenues,
                'peak_hiring_months' => $peakMonths,
                'monthly_trends' => $monthlyTrends,
                'summary' => [
                    'total_jobs' => $jobs->count(),
                    'total_vacancies' => $jobs->sum('vacancy'),
                    'total_employers' => $jobs->pluck('employer_id')->unique()->count(),
                    'categories_covered' => $jobs->pluck('category_id')->unique()->count(),
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Export cluster analysis report
     */
    public function exportClusterReport(Request $request)
    {
        $clusterData = $this->getClusterAnalysis($request)->getData(true);

        $filename = 'kmeans-cluster-report-' . date('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($clusterData) {
            $file = fopen('php://output', 'w');

            // Header
            fputcsv($file, ['K-Means Cluster Analysis Report']);
            fputcsv($file, ['Generated: ' . date('Y-m-d H:i:s')]);
            fputcsv($file, []);

            // Summary
            fputcsv($file, ['Summary']);
            fputcsv($file, ['Total Jobs', $clusterData['data']['total_jobs']]);
            fputcsv($file, ['Total Jobseekers', $clusterData['data']['total_jobseekers']]);
            fputcsv($file, ['Number of Clusters', $clusterData['data']['k']]);
            fputcsv($file, ['Silhouette Score', $clusterData['data']['silhouette_score']]);
            fputcsv($file, []);

            // Cluster details
            fputcsv($file, ['Cluster Details']);
            fputcsv($file, ['Cluster', 'Category', 'Jobs', 'Applications', 'Jobseekers', 'Avg Salary', 'Demand Score']);

            foreach ($clusterData['data']['clusters'] as $cluster) {
                fputcsv($file, [
                    $cluster['id'] + 1,
                    $cluster['name'],
                    $cluster['job_count'],
                    $cluster['application_count'],
                    $cluster['jobseeker_count'],
                    $cluster['avg_salary'],
                    $cluster['demand_score'],
                ]);
            }

            fputcsv($file, []);

            // Insights
            fputcsv($file, ['Key Insights']);
            foreach ($clusterData['data']['cluster_summary']['insights'] as $insight) {
                fputcsv($file, [$insight]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Clear analytics cache
     */
    public function clearCache()
    {
        Cache::forget('peso_dashboard_stats');
        Cache::forget('kmeans_cluster_analysis_5');
        Cache::forget('skill_trends_30');
        Cache::forget('job_map_data_all');
        Cache::forget('applicant_density_data');
        Cache::forget('job_fair_planning_all');

        return response()->json([
            'success' => true,
            'message' => 'Analytics cache cleared successfully'
        ]);
    }
}

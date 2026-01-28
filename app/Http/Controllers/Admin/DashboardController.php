<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\KycData;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Cache dashboard statistics for 5 minutes to improve performance
        $stats = \Cache::remember('admin_dashboard_stats', 300, function () {
            // Get current month and previous month dates
            $now = Carbon::now();
            $currentMonthStart = $now->copy()->startOfMonth();
            $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
            $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

            // Calculate total users and growth
            $totalUsers = User::count();
            $lastMonthUsers = User::where('created_at', '<=', $lastMonthEnd)->count();
            $userGrowth = $lastMonthUsers > 0 ? round(($totalUsers - $lastMonthUsers) / $lastMonthUsers * 100, 1) : 0;

            // Calculate job statistics
            $pendingJobs = Job::where('status', Job::STATUS_PENDING)->count();
            $approvedJobs = Job::where('status', Job::STATUS_APPROVED)->count();
            $rejectedJobs = Job::where('status', Job::STATUS_REJECTED)->count();
            $totalJobs = Job::count();
            $activeJobs = $approvedJobs; // For backward compatibility with view

            // Calculate growth for approved jobs
            $lastMonthApprovedJobs = Job::where('status', Job::STATUS_APPROVED)
                ->where('created_at', '<=', $lastMonthEnd)
                ->count();
            $jobGrowth = $lastMonthApprovedJobs > 0 ? round(($approvedJobs - $lastMonthApprovedJobs) / $lastMonthApprovedJobs * 100, 1) : 0;

            // Get KYC status counts
            $verifiedKyc = User::where('kyc_status', 'verified')->count();
            $pendingKyc = User::where('kyc_status', 'in_progress')->count();
            $rejectedKyc = User::where('kyc_status', 'rejected')->count();
            $totalKycRequired = User::where('role', 'jobseeker')->count();

            // Calculate total applications and growth
            $totalApplications = JobApplication::count();
            $lastMonthApplications = JobApplication::where('created_at', '<=', $lastMonthEnd)->count();
            $applicationGrowth = $lastMonthApplications > 0
                ? round(($totalApplications - $lastMonthApplications) / $lastMonthApplications * 100, 1)
                : 0;

            // Get registration data for the last 30 days (Users vs Jobs)
            $chartData = collect();
            for ($i = 29; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i)->format('Y-m-d');
                $displayDate = Carbon::now()->subDays($i)->format('M d');

                $userCount = User::whereDate('created_at', $date)->count();
                $jobCount = Job::whereDate('created_at', $date)->count();

                $chartData->push([
                    'date' => $displayDate,
                    'users' => $userCount,
                    'jobs' => $jobCount
                ]);
            }

            // Get user distribution by role
            $userTypeData = [
                User::where('role', 'jobseeker')->count(),
                User::where('role', 'employer')->count(),
                User::where('role', 'admin')->count()
            ];

            return compact(
                'totalUsers',
                'userGrowth',
                'activeJobs',
                'jobGrowth',
                'pendingJobs',
                'approvedJobs',
                'rejectedJobs',
                'totalJobs',
                'verifiedKyc',
                'pendingKyc',
                'rejectedKyc',
                'totalKycRequired',
                'totalApplications',
                'applicationGrowth',
                'chartData',
                'userTypeData'
            );
        });

        return view('admin.dashboard', $stats);
    }

    /**
     * Get real-time dashboard statistics for AJAX updates
     */
    public function getStats()
    {
        // Get current month and previous month dates
        $now = Carbon::now();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        // Calculate total users and growth
        $totalUsers = User::count();
        $lastMonthUsers = User::where('created_at', '<=', $lastMonthEnd)->count();
        $userGrowth = $lastMonthUsers > 0 ? round(($totalUsers - $lastMonthUsers) / $lastMonthUsers * 100, 1) : 0;

        // Calculate job statistics
        $pendingJobs = Job::where('status', Job::STATUS_PENDING)->count();
        $approvedJobs = Job::where('status', Job::STATUS_APPROVED)->count();
        $rejectedJobs = Job::where('status', Job::STATUS_REJECTED)->count();
        $totalJobs = Job::count();
        $activeJobs = $approvedJobs; // For backward compatibility

        // Calculate growth for approved jobs
        $lastMonthApprovedJobs = Job::where('status', Job::STATUS_APPROVED)
            ->where('created_at', '<=', $lastMonthEnd)
            ->count();
        $jobGrowth = $lastMonthApprovedJobs > 0 ? round(($approvedJobs - $lastMonthApprovedJobs) / $lastMonthApprovedJobs * 100, 1) : 0;

        // Get KYC status counts
        $verifiedKyc = User::where('kyc_status', 'verified')->count();
        $pendingKyc = User::where('kyc_status', 'in_progress')->count();
        $rejectedKyc = User::where('kyc_status', 'rejected')->count();

        // Calculate total applications and growth
        $totalApplications = JobApplication::count();
        $lastMonthApplications = JobApplication::where('created_at', '<=', $lastMonthEnd)->count();
        $applicationGrowth = $lastMonthApplications > 0
            ? round(($totalApplications - $lastMonthApplications) / $lastMonthApplications * 100, 1)
            : 0;

        // Get chart data for the last 30 days
        $chartData = collect();
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $displayDate = Carbon::now()->subDays($i)->format('M d');

            $userCount = User::whereDate('created_at', $date)->count();
            $jobCount = Job::whereDate('created_at', $date)->count();

            $chartData->push([
                'date' => $displayDate,
                'users' => $userCount,
                'jobs' => $jobCount
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'totalUsers' => $totalUsers,
                'userGrowth' => $userGrowth,
                'activeJobs' => $activeJobs,
                'jobGrowth' => $jobGrowth,
                'pendingJobs' => $pendingJobs,
                'approvedJobs' => $approvedJobs,
                'rejectedJobs' => $rejectedJobs,
                'totalJobs' => $totalJobs,
                'verifiedKyc' => $verifiedKyc,
                'pendingKyc' => $pendingKyc,
                'rejectedKyc' => $rejectedKyc,
                'totalApplications' => $totalApplications,
                'applicationGrowth' => $applicationGrowth,
                'chartData' => $chartData,
                'lastUpdated' => now()->format('H:i:s')
            ]
        ]);
    }

    public function analytics(Request $request)
    {
        // If it's an AJAX request for chart data
        if ($request->has('type') && ($request->ajax() || $request->wantsJson())) {
            return $this->getAnalyticsData($request);
        }

        // If it's an AJAX request for stats refresh
        if ($request->ajax() || $request->wantsJson()) {
            return $this->getCompanyStats();
        }

        // Otherwise, return the analytics view
        $days = 30;
        $startDate = Carbon::now()->subDays($days);

        // Get summary statistics
        $totalJobs = Job::count();
        $totalApplications = JobApplication::count();
        $totalUsers = User::count();
        $activeJobs = Job::where('status', Job::STATUS_APPROVED)->count();

        // Get job statistics by status
        $pendingJobs = Job::where('status', Job::STATUS_PENDING)->count();
        $approvedJobs = Job::where('status', Job::STATUS_APPROVED)->count();
        $rejectedJobs = Job::where('status', Job::STATUS_REJECTED)->count();

        // Get application statistics by status
        $pendingApplications = JobApplication::where('status', 'pending')->count();
        $acceptedApplications = JobApplication::where('status', 'accepted')->count();
        $rejectedApplications = JobApplication::where('status', 'rejected')->count();

        // Get top categories
        $topCategories = Job::select('categories.name', DB::raw('count(*) as count'))
            ->join('categories', 'jobs.category_id', '=', 'categories.id')
            ->where('jobs.status', Job::STATUS_APPROVED)
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // Company Analytics
        $totalCompanies = User::where('role', 'employer')->count();

        // Active companies (posted at least 1 job)
        $activeCompanies = User::where('role', 'employer')
            ->whereHas('jobs')
            ->count();

        // Inactive companies (no jobs posted)
        $inactiveCompanies = $totalCompanies - $activeCompanies;

        // Top companies by job count
        $topCompaniesByJobs = User::where('role', 'employer')
            ->withCount('jobs')
            ->having('jobs_count', '>', 0)
            ->orderByDesc('jobs_count')
            ->limit(10)
            ->get();

        // Top companies by applications received
        $topCompaniesByApplications = User::where('role', 'employer')
            ->withCount([
                'jobs as applications_count' => function ($query) {
                    $query->join('job_applications', 'jobs.id', '=', 'job_applications.job_id');
                }
            ])
            ->having('applications_count', '>', 0)
            ->orderByDesc('applications_count')
            ->limit(10)
            ->get();

        // Verified vs Unverified companies
        $verifiedCompanies = User::where('role', 'employer')
            ->whereNotNull('email_verified_at')
            ->count();
        $unverifiedCompanies = $totalCompanies - $verifiedCompanies;

        return view('admin.analytics', compact(
            'totalJobs',
            'totalApplications',
            'totalUsers',
            'activeJobs',
            'pendingJobs',
            'approvedJobs',
            'rejectedJobs',
            'pendingApplications',
            'acceptedApplications',
            'rejectedApplications',
            'topCategories',
            'totalCompanies',
            'activeCompanies',
            'inactiveCompanies',
            'topCompaniesByJobs',
            'topCompaniesByApplications',
            'verifiedCompanies',
            'unverifiedCompanies'
        ));
    }

    private function getCompanyStats()
    {
        $totalCompanies = User::where('role', 'employer')->count();
        $activeCompanies = User::where('role', 'employer')->whereHas('jobs')->count();
        $inactiveCompanies = $totalCompanies - $activeCompanies;
        $verifiedCompanies = User::where('role', 'employer')->whereNotNull('email_verified_at')->count();
        $unverifiedCompanies = $totalCompanies - $verifiedCompanies;

        // Top companies by jobs
        $topCompaniesByJobs = User::where('role', 'employer')
            ->withCount('jobs')
            ->having('jobs_count', '>', 0)
            ->orderByDesc('jobs_count')
            ->limit(10)
            ->get()
            ->map(function ($company) {
                return [
                    'name' => $company->name,
                    'jobs_count' => $company->jobs_count
                ];
            });

        // Top companies by applications
        $topCompaniesByApplications = User::where('role', 'employer')
            ->withCount([
                'jobs as applications_count' => function ($query) {
                    $query->join('job_applications', 'jobs.id', '=', 'job_applications.job_id');
                }
            ])
            ->having('applications_count', '>', 0)
            ->orderByDesc('applications_count')
            ->limit(10)
            ->get()
            ->map(function ($company) {
                return [
                    'name' => $company->name,
                    'applications_count' => $company->applications_count
                ];
            });

        return response()->json([
            'companyStats' => [
                'totalCompanies' => $totalCompanies,
                'activeCompanies' => $activeCompanies,
                'inactiveCompanies' => $inactiveCompanies,
                'verifiedCompanies' => $verifiedCompanies,
                'unverifiedCompanies' => $unverifiedCompanies,
                'topCompaniesByJobs' => $topCompaniesByJobs,
                'topCompaniesByApplications' => $topCompaniesByApplications
            ]
        ]);
    }

    private function getAnalyticsData(Request $request)
    {
        $days = $request->get('days', 30);
        $type = $request->get('type', 'jobs');
        $startDate = Carbon::now()->subDays($days);

        switch ($type) {
            case 'jobs':
                $data = Job::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                    ->where('created_at', '>=', $startDate)
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
                break;

            case 'applications':
                $data = JobApplication::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                    ->where('created_at', '>=', $startDate)
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
                break;

            case 'users':
                $data = User::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                    ->where('created_at', '>=', $startDate)
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
                break;

            case 'companies':
                $data = User::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                    ->where('role', 'employer')
                    ->where('created_at', '>=', $startDate)
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
                break;

            default:
                return response()->json(['error' => 'Invalid chart type'], 400);
        }

        // Fill in missing dates with zero values
        $dateRange = collect();
        for ($date = $startDate->copy(); $date->lte(Carbon::now()); $date->addDay()) {
            $dateRange->push($date->format('Y-m-d'));
        }

        $formattedData = $dateRange->map(function ($date) use ($data) {
            $value = $data->firstWhere('date', $date);
            return [
                'date' => $date,
                'count' => $value ? $value->count : 0
            ];
        });

        return response()->json([
            'labels' => $formattedData->pluck('date')->map(function ($date) {
                return Carbon::parse($date)->format('M d');
            }),
            'values' => $formattedData->pluck('count')
        ]);
    }

    /**
     * Export dashboard statistics to Excel/CSV
     */
    public function exportStatistics(Request $request)
    {
        $format = $request->get('format', 'csv'); // csv or excel

        // Get current statistics
        $now = Carbon::now();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        $data = [];
        $data[] = ['Metric', 'Count', 'Growth %', 'Last Updated'];
        $data[] = [];

        // User Statistics
        $totalUsers = User::count();
        $lastMonthUsers = User::where('created_at', '<=', $lastMonthEnd)->count();
        $userGrowth = $lastMonthUsers > 0 ? round(($totalUsers - $lastMonthUsers) / $lastMonthUsers * 100, 1) : 0;
        $data[] = ['Total Users', $totalUsers, $userGrowth . '%', $now->format('Y-m-d H:i:s')];

        // Job Seekers
        $jobSeekers = User::where('role', 'jobseeker')->count();
        $data[] = ['Job Seekers', $jobSeekers, '', ''];

        // Employers
        $employers = User::where('role', 'employer')->count();
        $data[] = ['Employers', $employers, '', ''];

        // Admins
        $admins = User::where('role', 'admin')->count();
        $data[] = ['Administrators', $admins, '', ''];

        $data[] = [];

        // Job Statistics
        $activeJobs = Job::where('status', Job::STATUS_APPROVED)->count();
        $pendingJobs = Job::where('status', Job::STATUS_PENDING)->count();
        $rejectedJobs = Job::where('status', Job::STATUS_REJECTED)->count();
        $totalJobs = Job::count();

        $data[] = ['Active Jobs', $activeJobs, '', ''];
        $data[] = ['Pending Jobs', $pendingJobs, '', ''];
        $data[] = ['Rejected Jobs', $rejectedJobs, '', ''];
        $data[] = ['Total Jobs', $totalJobs, '', ''];

        $data[] = [];

        // KYC Statistics
        $verifiedKyc = User::where('kyc_status', 'verified')->count();
        $pendingKyc = User::where('kyc_status', 'in_progress')->count();
        $rejectedKyc = User::where('kyc_status', 'rejected')->count();

        $data[] = ['KYC Verified', $verifiedKyc, '', ''];
        $data[] = ['KYC Pending', $pendingKyc, '', ''];
        $data[] = ['KYC Rejected', $rejectedKyc, '', ''];

        $data[] = [];

        // Application Statistics
        $totalApplications = JobApplication::count();
        $lastMonthApplications = JobApplication::where('created_at', '<=', $lastMonthEnd)->count();
        $applicationGrowth = $lastMonthApplications > 0
            ? round(($totalApplications - $lastMonthApplications) / $lastMonthApplications * 100, 1)
            : 0;

        $data[] = ['Total Applications', $totalApplications, $applicationGrowth . '%', ''];

        // Generate filename
        $filename = 'admin-dashboard-stats-' . date('Y-m-d-His') . '.csv';

        // Create response
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            foreach ($data as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Clear dashboard cache (manual refresh)
     */
    public function clearCache()
    {
        \Cache::forget('admin_dashboard_stats');

        return response()->json([
            'success' => true,
            'message' => 'Dashboard cache cleared successfully. Page will refresh.'
        ]);
    }
}

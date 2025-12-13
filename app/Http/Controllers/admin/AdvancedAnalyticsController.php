<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdvancedAnalyticsController extends Controller
{
    /**
     * Display advanced analytics dashboard
     */
    public function index()
    {
        $dateRange = request('range', '30'); // Default 30 days
        $startDate = Carbon::now()->subDays($dateRange);

        // Get summary metrics
        $metrics = $this->getSummaryMetrics($startDate);

        return view('admin.analytics.advanced', compact('metrics', 'dateRange'));
    }

    /**
     * Get user activity heatmap data
     */
    public function getUserActivityHeatmap(Request $request)
    {
        $days = $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days);

        // Get hourly user activity (registrations + logins)
        $activityData = [];

        // Registration activity by hour and day of week
        $registrations = User::select(
            DB::raw('DAYOFWEEK(created_at) as day_of_week'),
            DB::raw('HOUR(created_at) as hour'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', $startDate)
            ->groupBy('day_of_week', 'hour')
            ->get();

        // Format data for heatmap
        $heatmapData = [];
        for ($day = 0; $day < 7; $day++) {
            for ($hour = 0; $hour < 24; $hour++) {
                $heatmapData[$day][$hour] = 0;
            }
        }

        foreach ($registrations as $reg) {
            $dayIndex = $reg->day_of_week - 1; // Convert to 0-6
            $heatmapData[$dayIndex][$reg->hour] = $reg->count;
        }

        return response()->json([
            'data' => $heatmapData,
            'days' => ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
            'hours' => range(0, 23)
        ]);
    }

    /**
     * Get job posting trends by category
     */
    public function getJobTrendsByCategory(Request $request)
    {
        $days = $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days);

        $trends = Job::select(
            'categories.name as category',
            DB::raw('COUNT(*) as total_jobs'),
            DB::raw('SUM(CASE WHEN jobs.status = ' . Job::STATUS_APPROVED . ' THEN 1 ELSE 0 END) as active_jobs'),
            DB::raw('AVG(jobs.vacancy) as avg_vacancies')
        )
            ->join('categories', 'jobs.category_id', '=', 'categories.id')
            ->where('jobs.created_at', '>=', $startDate)
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_jobs')
            ->limit(10)
            ->get();

        return response()->json($trends);
    }

    /**
     * Get job posting trends by location
     */
    public function getJobTrendsByLocation(Request $request)
    {
        $days = $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days);

        $trends = Job::select(
            'location',
            DB::raw('COUNT(*) as job_count'),
            DB::raw('SUM(vacancy) as total_vacancies'),
            DB::raw('AVG(CASE WHEN salary_min > 0 THEN (salary_min + salary_max) / 2 ELSE 0 END) as avg_salary')
        )
            ->where('created_at', '>=', $startDate)
            ->where('status', Job::STATUS_APPROVED)
            ->groupBy('location')
            ->orderByDesc('job_count')
            ->limit(15)
            ->get();

        return response()->json($trends);
    }

    /**
     * Get application conversion rates
     */
    public function getConversionRates(Request $request)
    {
        $days = $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days);

        // Overall conversion funnel
        $totalUsers = User::where('role', 'jobseeker')
            ->where('created_at', '>=', $startDate)
            ->count();

        $usersWithApplications = User::where('role', 'jobseeker')
            ->where('created_at', '>=', $startDate)
            ->whereHas('jobApplications')
            ->count();

        $totalApplications = JobApplication::where('created_at', '>=', $startDate)->count();

        $acceptedApplications = JobApplication::where('created_at', '>=', $startDate)
            ->where('status', 'accepted')
            ->count();

        // Calculate conversion rates
        $applicationRate = $totalUsers > 0 ? round(($usersWithApplications / $totalUsers) * 100, 2) : 0;
        $acceptanceRate = $totalApplications > 0 ? round(($acceptedApplications / $totalApplications) * 100, 2) : 0;

        // Category-wise conversion rates
        $categoryConversions = Job::select(
            'categories.name as category',
            DB::raw('COUNT(DISTINCT job_applications.id) as applications'),
            DB::raw('SUM(CASE WHEN job_applications.status = "accepted" THEN 1 ELSE 0 END) as accepted'),
            DB::raw('ROUND(SUM(CASE WHEN job_applications.status = "accepted" THEN 1 ELSE 0 END) / COUNT(job_applications.id) * 100, 2) as conversion_rate')
        )
            ->join('categories', 'jobs.category_id', '=', 'categories.id')
            ->leftJoin('job_applications', 'jobs.id', '=', 'job_applications.job_id')
            ->where('job_applications.created_at', '>=', $startDate)
            ->groupBy('categories.id', 'categories.name')
            ->having('applications', '>', 0)
            ->orderByDesc('conversion_rate')
            ->limit(10)
            ->get();

        return response()->json([
            'funnel' => [
                'total_users' => $totalUsers,
                'users_with_applications' => $usersWithApplications,
                'total_applications' => $totalApplications,
                'accepted_applications' => $acceptedApplications,
                'application_rate' => $applicationRate,
                'acceptance_rate' => $acceptanceRate
            ],
            'by_category' => $categoryConversions
        ]);
    }

    /**
     * Get peak hiring times analysis
     */
    public function getPeakHiringTimes(Request $request)
    {
        $days = $request->get('days', 90); // Default 90 days for better patterns
        $startDate = Carbon::now()->subDays($days);

        // Job postings by day of week
        $jobsByDayOfWeek = Job::select(
            DB::raw('DAYOFWEEK(created_at) as day_of_week'),
            DB::raw('DAYNAME(created_at) as day_name'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', $startDate)
            ->groupBy('day_of_week', 'day_name')
            ->orderBy('day_of_week')
            ->get();

        // Applications by day of week
        $applicationsByDayOfWeek = JobApplication::select(
            DB::raw('DAYOFWEEK(created_at) as day_of_week'),
            DB::raw('DAYNAME(created_at) as day_name'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', $startDate)
            ->groupBy('day_of_week', 'day_name')
            ->orderBy('day_of_week')
            ->get();

        // Hiring by month
        $hiringByMonth = Job::select(
            DB::raw('MONTHNAME(created_at) as month'),
            DB::raw('YEAR(created_at) as year'),
            DB::raw('COUNT(*) as job_count'),
            DB::raw('SUM(vacancy) as total_vacancies')
        )
            ->where('created_at', '>=', $startDate)
            ->where('status', Job::STATUS_APPROVED)
            ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'), DB::raw('MONTHNAME(created_at)'))
            ->orderBy('year')
            ->orderBy(DB::raw('MONTH(created_at)'))
            ->get();

        // Peak hours for applications
        $peakApplicationHours = JobApplication::select(
            DB::raw('HOUR(created_at) as hour'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', $startDate)
            ->groupBy('hour')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        return response()->json([
            'jobs_by_day' => $jobsByDayOfWeek,
            'applications_by_day' => $applicationsByDayOfWeek,
            'hiring_by_month' => $hiringByMonth,
            'peak_hours' => $peakApplicationHours
        ]);
    }

    /**
     * Get time series data for trend analysis
     */
    public function getTimeSeriesData(Request $request)
    {
        $days = $request->get('days', 30);
        $type = $request->get('type', 'all'); // all, jobs, applications, users
        $startDate = Carbon::now()->subDays($days);

        $data = [];

        if ($type === 'all' || $type === 'jobs') {
            $jobs = Job::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
                ->where('created_at', '>=', $startDate)
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->keyBy('date');

            $data['jobs'] = $jobs;
        }

        if ($type === 'all' || $type === 'applications') {
            $applications = JobApplication::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
                ->where('created_at', '>=', $startDate)
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->keyBy('date');

            $data['applications'] = $applications;
        }

        if ($type === 'all' || $type === 'users') {
            $users = User::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
                ->where('created_at', '>=', $startDate)
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->keyBy('date');

            $data['users'] = $users;
        }

        // Fill in missing dates
        $dateRange = [];
        for ($date = $startDate->copy(); $date->lte(Carbon::now()); $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $dateRange[] = [
                'date' => $dateStr,
                'jobs' => isset($data['jobs'][$dateStr]) ? $data['jobs'][$dateStr]->count : 0,
                'applications' => isset($data['applications'][$dateStr]) ? $data['applications'][$dateStr]->count : 0,
                'users' => isset($data['users'][$dateStr]) ? $data['users'][$dateStr]->count : 0,
            ];
        }

        return response()->json($dateRange);
    }

    /**
     * Get summary metrics for dashboard
     */
    private function getSummaryMetrics($startDate)
    {
        return [
            'total_jobs' => Job::where('created_at', '>=', $startDate)->count(),
            'active_jobs' => Job::where('status', Job::STATUS_APPROVED)
                ->where('created_at', '>=', $startDate)
                ->count(),
            'total_applications' => JobApplication::where('created_at', '>=', $startDate)->count(),
            'acceptance_rate' => $this->calculateAcceptanceRate($startDate),
            'new_users' => User::where('created_at', '>=', $startDate)->count(),
            'avg_time_to_hire' => $this->calculateAvgTimeToHire($startDate),
        ];
    }

    /**
     * Calculate acceptance rate
     */
    private function calculateAcceptanceRate($startDate)
    {
        $total = JobApplication::where('created_at', '>=', $startDate)->count();
        $accepted = JobApplication::where('created_at', '>=', $startDate)
            ->where('status', 'accepted')
            ->count();

        return $total > 0 ? round(($accepted / $total) * 100, 2) : 0;
    }

    /**
     * Calculate average time to hire
     */
    private function calculateAvgTimeToHire($startDate)
    {
        $avgDays = JobApplication::where('job_applications.created_at', '>=', $startDate)
            ->where('status', 'accepted')
            ->whereNotNull('updated_at')
            ->selectRaw('AVG(DATEDIFF(updated_at, created_at)) as avg_days')
            ->value('avg_days');

        return round($avgDays ?? 0, 1);
    }
}

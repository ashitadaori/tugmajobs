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
        // Get current month and previous month dates
        $now = Carbon::now();
        $currentMonthStart = $now->copy()->startOfMonth();
        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        // Calculate total users and growth
        $totalUsers = User::count();
        $lastMonthUsers = User::where('created_at', '<=', $lastMonthEnd)->count();
        $userGrowth = $lastMonthUsers > 0 ? round(($totalUsers - $lastMonthUsers) / $lastMonthUsers * 100, 1) : 0;

        // Calculate active jobs and growth
        $activeJobs = Job::where('status', 'active')->count();
        $lastMonthJobs = Job::where('status', 'active')
            ->where('created_at', '<=', $lastMonthEnd)
            ->count();
        $jobGrowth = $lastMonthJobs > 0 ? round(($activeJobs - $lastMonthJobs) / $lastMonthJobs * 100, 1) : 0;

        // Get pending KYC count and change (users with in_progress status)
        $pendingKyc = User::where('kyc_status', 'in_progress')->count();
        $lastMonthPendingKyc = User::where('kyc_status', 'in_progress')
            ->where('created_at', '<=', $lastMonthEnd)
            ->count();
        $kycChange = $pendingKyc - $lastMonthPendingKyc;

        // Calculate total applications and growth
        $totalApplications = JobApplication::count();
        $lastMonthApplications = JobApplication::where('created_at', '<=', $lastMonthEnd)->count();
        $applicationGrowth = $lastMonthApplications > 0 
            ? round(($totalApplications - $lastMonthApplications) / $lastMonthApplications * 100, 1) 
            : 0;

        // Get registration data for the last 7 days
        $registrationData = User::select(
            DB::raw('DATE_FORMAT(created_at, "%a") as day'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('day')
            ->orderBy('created_at')
            ->get();

        // Get user distribution by role
        $userTypeData = [
            User::where('role', 'job_seeker')->count(),
            User::where('role', 'employer')->count(),
            User::where('role', 'admin')->count()
        ];

        return view('admin.dashboard', compact(
            'totalUsers',
            'userGrowth',
            'activeJobs',
            'jobGrowth',
            'pendingKyc',
            'kycChange',
            'totalApplications',
            'applicationGrowth',
            'registrationData',
            'userTypeData'
        ));
    }

    public function analytics(Request $request)
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
}

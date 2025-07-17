<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Job;
use App\Models\JobType;
use App\Models\JobView;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HomeController extends Controller
{
    // This method will show home page
    public function index(){
        // Get featured jobs
        $featuredJobs = Job::where('status', 1)
                        ->where('featured', 1)
                        ->with(['jobType', 'employer.employerProfile'])
                        ->orderBy('created_at', 'DESC')
                        ->take(6)
                        ->get();

        // Get latest jobs
        $latestJobs = Job::where('status', 1)
                        ->with(['jobType', 'employer.employerProfile'])
                        ->orderBy('created_at', 'DESC')
                        ->take(6)
                        ->get();

        // Get active categories with job counts
        $categories = Category::where('status', 1)
                        ->withCount(['jobs' => function($query) {
                            $query->where('status', 1);
                        }])
                        ->orderBy('jobs_count', 'DESC')
                        ->take(8)
                        ->get();

        // Get all categories for the full listing
        $allCategories = Category::where('status', 1)->get();
        
        // Get all job types
        $jobTypes = JobType::where('status', 1)->get();

        // Get job statistics
        $stats = [
            'total_jobs' => Job::where('status', 1)->count(),
            'companies' => DB::table('employer_profiles')->count(),
            'applications' => JobApplication::count(),
            'jobs_this_week' => Job::where('status', 1)
                                ->where('created_at', '>=', Carbon::now()->subWeek())
                                ->count()
        ];

        // Get trending job types based on views and applications
        $trendingJobTypes = JobType::select('job_types.name', 'job_types.id')
            ->join('jobs', 'jobs.job_type_id', '=', 'job_types.id')
            ->join('job_views', 'job_views.job_id', '=', 'jobs.id')
            ->where('job_types.status', 1)
            ->where('jobs.status', 1)
            ->groupBy('job_types.id', 'job_types.name')
            ->orderByRaw('COUNT(job_views.id) DESC')
            ->take(5)
            ->get();

        // Get popular keywords from recent job titles
        $popularKeywords = Job::where('status', 1)
            ->where('created_at', '>=', Carbon::now()->subMonths(3))
            ->selectRaw('LOWER(SUBSTRING_INDEX(title, " ", 1)) as keyword')
            ->groupBy('keyword')
            ->orderByRaw('COUNT(*) DESC')
            ->take(5)
            ->pluck('keyword');



        // Banner images for carousel
        $bannerImages = [
            ['image' => 'banner-1.jpg', 'title' => 'Find Your Dream Job', 'subtitle' => 'Discover opportunities that match your skills'],
            ['image' => 'banner-2.jpg', 'title' => 'Connect with Top Employers', 'subtitle' => 'Build your career with leading companies'],
            ['image' => 'banner3.jpg', 'title' => 'Work in Digos City', 'subtitle' => 'Local opportunities for local talent'],
            ['image' => 'banner4.jpg', 'title' => 'Remote Work Options', 'subtitle' => 'Find flexible work arrangements'],
            ['image' => 'banner5.jpg', 'title' => 'Start Your Journey', 'subtitle' => 'Your next career move starts here']
        ];

        return view('front.modern-home', [
            'featuredJobs' => $featuredJobs,
            'latestJobs' => $latestJobs,
            'categories' => $categories,
            'allCategories' => $allCategories,
            'jobTypes' => $jobTypes,
            'stats' => $stats,
            'trendingJobTypes' => $trendingJobTypes,
            'popularKeywords' => $popularKeywords,
            'bannerImages' => $bannerImages
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Job;
use App\Models\JobType;
use App\Models\JobView;
use App\Models\JobApplication;
use App\Models\Employer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HomeController extends Controller
{
    // This method will show home page
    public function index(Request $request)
    {
        // Redirect authenticated jobseekers to their dashboard unless they specifically want the homepage
        if (Auth::check() && Auth::user()->isJobSeeker() && !$request->has('force_home')) {
            return redirect()->route('account.dashboard');
        }
        // Get featured jobs - show latest active jobs (status=1 means active/approved)
        $featuredJobs = Job::where('status', 1)
            ->with(['jobType', 'employer.employerProfile', 'employerCompany', 'category'])
            ->withCount('applications')
            ->orderBy('created_at', 'DESC')
            ->take(8)
            ->get();

        // Get latest jobs (showing newest active jobs)
        $latestJobs = Job::where('status', 1)
            ->with(['jobType', 'employer.employerProfile', 'employerCompany', 'category'])
            ->withCount('applications')
            ->orderBy('created_at', 'DESC')
            ->take(6)
            ->get();

        // Get active categories with job counts
        $categories = Category::where('status', 1)
            ->withCount([
                'jobs' => function ($query) {
                    $query->where('status', 1);
                }
            ])
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
            'companies' => DB::table('employers')->count(),
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

        // Get popular keywords - mix of trending job titles and common search terms
        $trendingKeywords = Job::where('status', 1)
            ->where('created_at', '>=', Carbon::now()->subMonths(3))
            ->selectRaw('LOWER(SUBSTRING_INDEX(title, " ", 1)) as keyword')
            ->groupBy('keyword')
            ->orderByRaw('COUNT(*) DESC')
            ->take(3)
            ->pluck('keyword')
            ->toArray();

        // Add common popular search terms
        $commonKeywords = ['senior', 'junior', 'manager', 'remote', 'part-time', 'full-time'];

        // Combine trending and common keywords, remove duplicates and empty values
        $popularKeywords = array_unique(array_merge($trendingKeywords, $commonKeywords));
        $popularKeywords = array_filter($popularKeywords, function ($keyword) {
            return !empty($keyword) && strlen($keyword) > 2;
        });

        // Take only 6 keywords and shuffle for variety
        $popularKeywords = array_slice($popularKeywords, 0, 6);

        // Get featured companies - combine standalone companies and employer profiles
        $standaloneCompanies = \App\Models\Company::with([
            'jobs' => function ($query) {
                $query->where('status', 1)->orderBy('created_at', 'desc');
            }
        ])
            ->whereHas('jobs', function ($query) {
                $query->where('status', 1);
            })
            ->orderBy('created_at', 'DESC')
            ->get()
            ->map(function ($company) {
                return (object) [
                    'id' => $company->id,
                    'name' => $company->name,
                    'company_name' => $company->name,
                    'company_description' => $company->description,
                    'company_logo' => $company->logo,
                    'location' => $company->location,
                    'website' => $company->website,
                    'jobs_count' => $company->jobs->count(),
                    'type' => 'standalone',
                    'slug' => $company->slug ?? null,
                    'average_rating' => null,
                    'reviews_count' => 0
                ];
            });

        $employerCompanies = Employer::with([
            'user',
            'jobs' => function ($query) {
                $query->where('status', 1)->orderBy('created_at', 'desc');
            }
        ])
            ->withCount([
                'reviews' => function ($query) {
                    $query->where('review_type', 'company');
                }
            ])
            ->whereHas('jobs', function ($query) {
                $query->where('status', 1);
            })
            ->whereNotNull('company_name')
            ->whereNotNull('company_description')
            ->orderBy('created_at', 'DESC')
            ->get()
            ->map(function ($employer) {
                // $reviewsCount = \App\Models\Review::where('employer_id', $employer->user_id)
                //     ->where('review_type', 'company')
                //     ->count();
                $averageRating = \App\Models\Review::getCompanyAverageRating($employer->user_id);

                return (object) [
                    'id' => $employer->user_id,
                    'name' => $employer->company_name,
                    'company_name' => $employer->company_name,
                    'company_description' => $employer->company_description,
                    'company_logo' => $employer->company_logo,
                    'location' => $employer->city,
                    'website' => $employer->company_website,
                    'jobs_count' => $employer->jobs->count(),
                    'type' => 'employer',
                    'user' => $employer->user,
                    'average_rating' => $averageRating ? round($averageRating, 1) : null,
                    'reviews_count' => $employer->reviews_count
                ];
            });

        // Merge and sort by most recent, take top 6
        $featuredCompanies = $standaloneCompanies->concat($employerCompanies)
            ->sortByDesc('reviews_count')
            ->take(6);

        // Banner images for carousel
        $bannerImages = [
            ['image' => 'banner-1.jpg', 'title' => 'Find Your Dream Job', 'subtitle' => 'Discover opportunities that match your skills'],
            ['image' => 'banner-2.jpg', 'title' => 'Connect with Top Employers', 'subtitle' => 'Build your career with leading companies'],
            ['image' => 'banner3.jpg', 'title' => 'Work in Sta. Cruz, Davao del Sur', 'subtitle' => 'Local opportunities for local talent'],
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
            'featuredCompanies' => $featuredCompanies,
            'bannerImages' => $bannerImages
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Employer;
use App\Models\Job;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index()
    {
        // Pagination settings
        $perPage = 12;
        $page = request()->get('page', 1);

        // 1. Build Union Query for lightweight pagination
        // Standalone companies query
        $standaloneQuery = \DB::table('companies')
            ->select('id', 'created_at', \DB::raw("'standalone' as source_type"))
            ->whereNull('deleted_at'); // Assuming SoftDeletes

        // Employer companies query
        // We need to join users to ensure the employer exists and has active jobs
        $employerQuery = \DB::table('employers')
            ->join('users', 'employers.user_id', '=', 'users.id')
            ->select('employers.id', 'employers.created_at', \DB::raw("'employer' as source_type"))
            ->distinct();

        // Count for pagination
        $totalStandalone = $standaloneQuery->count();
        $totalEmployer = $employerQuery->count();
        $total = $totalStandalone + $totalEmployer;

        // Fetch paginated IDs directly using UNION
        // Note: Union order by created_at is complex in some DBs, doing it in PHP for the page slice is okay 
        // IF we only fetch the lightweight columns. 
        // BUT strict database pagination is better. 

        // Optimized Strategy:
        // Since accurate global sorting of two large tables without a common index is slow,
        // and assuming "Recency" is the key, we will attempt to combine them.

        $results = $standaloneQuery->union($employerQuery)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // 2. Hydrate models for the current page
        $standaloneIds = [];
        $employerIds = [];

        foreach ($results as $item) {
            if ($item->source_type === 'standalone') {
                $standaloneIds[] = $item->id;
            } else {
                $employerIds[] = $item->id;
            }
        }

        // Fetch full models eagerly
        $standaloneModels = \App\Models\Company::with([
            'jobs' => function ($q) {
                $q->where('status', 1);
            }
        ])->whereIn('id', $standaloneIds)->get()->keyBy('id');

        $employerModels = Employer::with([
            'user',
            'jobs' => function ($q) {
                $q->where('status', 1);
            }
        ])->whereIn('id', $employerIds)->get()->keyBy('id');

        // 3. Transform back to uniform structure preserving the sort order
        $transformedCollection = collect($results->items())->map(function ($item) use ($standaloneModels, $employerModels) {
            if ($item->source_type === 'standalone') {
                $company = $standaloneModels->get($item->id);
                if (!$company)
                    return null;

                return (object) [
                    'id' => $company->id,
                    'company_name' => $company->name,
                    'company_description' => $company->description,
                    'location' => $company->location ?? 'Sta. Cruz, Davao del Sur',
                    'logo_url' => $company->logo ? 'storage/' . $company->logo : null,
                    'created_at' => $company->created_at,
                    'jobs' => $company->jobs,
                    'type' => 'standalone',
                    'average_rating' => null,
                    'reviews_count' => 0
                ];
            } else {
                $employer = $employerModels->get($item->id);
                if (!$employer)
                    return null;

                $reviewsCount = \App\Models\Review::where('employer_id', $employer->user_id)
                    ->where('review_type', 'company')
                    ->count();
                $averageRating = \App\Models\Review::getCompanyAverageRating($employer->user_id);

                return (object) [
                    'id' => $employer->id,
                    'company_name' => $employer->company_name ?? ($employer->user->name ?? 'Unknown'),
                    'company_description' => $employer->company_description,
                    'location' => $employer->city ?? 'Sta. Cruz, Davao del Sur',
                    'logo_url' => $employer->company_logo ? 'storage/' . $employer->company_logo : null,
                    'created_at' => $employer->created_at,
                    'jobs' => $employer->jobs,
                    'type' => 'employer',
                    'average_rating' => $averageRating ? round($averageRating, 1) : null,
                    'reviews_count' => $reviewsCount
                ];
            }
        })->filter(); // Remove nulls if any record wasn't found

        // Replace the collection in the paginator
        $companies = $results->setCollection($transformedCollection);

        return view('front.companies.index', compact('companies'));
    }

    public function show($id)
    {
        // Try to find as standalone company first (by ID or slug)
        $standaloneCompany = \App\Models\Company::where('id', $id)
            ->orWhere('slug', $id)
            ->first();

        if ($standaloneCompany) {
            $company = (object) [
                'id' => $standaloneCompany->id,
                'user_id' => null,
                'company_name' => $standaloneCompany->name,
                'company_description' => $standaloneCompany->description,
                'location' => $standaloneCompany->location ?? 'Sta. Cruz, Davao del Sur',
                'logo_url' => $standaloneCompany->logo ? 'storage/' . $standaloneCompany->logo : null,
                'website' => $standaloneCompany->website,
                'contact_email' => $standaloneCompany->email,
                'company_phone' => $standaloneCompany->phone,
                'industry' => $standaloneCompany->industry ?? null,
                'company_size' => $standaloneCompany->company_size ?? null,
                'founded_year' => $standaloneCompany->founded_year ?? null,
                'social_links' => $standaloneCompany->social_links ?? [],
                'created_at' => $standaloneCompany->created_at,
                'type' => 'standalone',
                'latitude' => $standaloneCompany->latitude ?? null,
                'longitude' => $standaloneCompany->longitude ?? null,
            ];

            $activeJobs = Job::where('company_id', $standaloneCompany->id)
                ->where('status', 1)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return view('front.companies.show', compact('company', 'activeJobs'));
        }

        // Try as employer profile by user_id first (this is what HomeController uses)
        $employer = Employer::with(['user'])->where('user_id', $id)->first();

        // If not found by user_id, try by employer id
        if (!$employer) {
            $employer = Employer::with(['user'])->find($id);
        }

        if (!$employer) {
            abort(404, 'Company not found');
        }

        // Increment profile views
        $employer->increment('profile_views');

        $company = (object) [
            'id' => $employer->id,
            'user_id' => $employer->user_id,
            'company_name' => $employer->company_name ?? $employer->user->name,
            'company_description' => $employer->company_description,
            'location' => $employer->city ?? 'Sta. Cruz, Davao del Sur',
            'logo_url' => $employer->company_logo ? 'storage/' . $employer->company_logo : null,
            'website' => $employer->company_website,
            'contact_email' => $employer->user->email,
            'company_phone' => $employer->business_phone,
            'industry' => $employer->industry ?? null,
            'company_size' => $employer->company_size ?? null,
            'founded_year' => $employer->founded_year ?? null,
            'social_links' => [
                'linkedin' => $employer->linkedin_url,
                'facebook' => $employer->facebook_url,
                'twitter' => $employer->twitter_url,
                'instagram' => $employer->instagram_url,
            ],
            'created_at' => $employer->created_at,
            'type' => 'employer',
            'latitude' => $employer->latitude ?? null,
            'longitude' => $employer->longitude ?? null,
        ];

        $activeJobs = Job::where('employer_id', $employer->user_id)
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('front.companies.show', compact('company', 'activeJobs'));
    }
}